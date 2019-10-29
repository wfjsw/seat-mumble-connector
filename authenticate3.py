#!/usr/bin/env python3

import Ice, sys
import math
import asyncio, aiohttp
import json
import socket

from datetime import datetime
# Ice.loadSlice('Murmur.ice')
import Murmur

user_dict = {}
req_headers = {
    'X-Token': 'SjG1IWcBk6LKwmRJliJJlnXguL3IUKEq'
}

all_user_url = 'https://seat.eve-info.net/mumble-connector/api/data'
login_evt_url = 'https://seat.eve-info.net/mumble-connector/api/login'
logout_evt_url = 'https://seat.eve-info.net/mumble-connector/api/logout'
hash_record_url = 'https://seat.eve-info.net/mumble-connector/api/postfp'

async def getPostedJSON(session, url, body):
    try:
        resp = await session.post(url, json=body)
        if resp.status == 200:
            return await resp.json()
        else:
            return None
    except:
        return None

async def postJSON(session, url, body):
    return await session.post(url, json=body)

async def fetchAllUser(client):
    global user_dict
    data = await getPostedJSON(client, all_user_url, {})
    if data != None:
        user_dict = data.get('data', {})
    return None

async def postLogin(data):
    async with aiohttp.ClientSession() as client:
        return await postJSON(client, login_evt_url, data)

async def postLogout(data):
    async with aiohttp.ClientSession() as client:
        return await postJSON(client, logout_evt_url, data)

async def postFp(data):
    async with aiohttp.ClientSession() as client:
        return await postJSON(client, hash_record_url, data)

async def pollUserFetch():
    async with aiohttp.ClientSession() as client:
        while True:
            await fetchAllUser(client)
            await asyncio.sleep(20)
        

def authenticate(name, pw, certhash):
    global user_dict
    if name in user_dict:
        user = user_dict[name]
        if pw == user['pw']:
            asyncio.create_task(postFp({
                'group_id': int(name),
                'cert_hash': certhash
            }))
            return (int(name), user.name, user.groups)
        else:
            return (-1, None, None)
    elif '-' in name:
        try:
            ud = name.split('-')
            user_id = int(ud[0])
            sub_id = int(ud[1])
            if str(user_id) not in user_dict:
                return (-1, None, None)
            if sub_id < 1 or sub_id > 4:
                return (-1, None, None)
            user = user_dict[str(user_id)]
            if pw == user['pw']:
                asyncio.create_task(postFp({
                    'group_id': int(name),
                    'cert_hash': certhash
                }))
                return (user_id + sub_id * math.pow(10, 8), '{}<sub{}>'.format(user.name[:55], sub_id), user.groups)
            else:
                return (-1, None, None)
        except:
            print('Multi-user error')
            return (-3, None, None)
    else:
        return (-1, None, None)



###
# mumble auth part
###

class ServerAuthenticatorI(Murmur.ServerUpdatingAuthenticator):
    global server
    def __init__(self, server, adapter):
        self.server = server

    def authenticate(self, name, pw, certificates, certhash, cerrstrong, out_newname):
        if name == 'Superuser':
            return (-2, None, None)
        charid, newname, groups = authenticate(name, pw, certhash)
        return (charid, newname, groups)

    def createChannel(name, server, id):
        return -2
        
    def registerPlayer(self, name, current=None):
        print("Someone tried to register " + name)
        return -1

    def idToTexture(self, id, current=None):
        return None

    def idToName(self, id, current=None):
        return None

    def nameToId(self, name, current=None):
        return -2

    def unregisterPlayer(self, id, current=None):
      return -1

    def getRegistration(self, id, current=None):
      return (-2, None, None)

    def getInfo(self, id, current = None):
        return (False, None)

    def setInfo(self, id, info, current = None):
        return -1

    def setTexture(self, id, texture, current = None):
        return -1
    def registerUser(self, name, current = None):
        return -1
    def unregisterUser(self, name, current = None):
        return -1
    def getRegisteredUsers(self, filter, current=None):
        return dict()

class ServerCallbackI(Murmur.ServerCallback):
    global server
    def __init__(self, server, adapter):
        self.server = server
    
    def userConnected(self, state, current=None):
        ip = "{}.{}.{}.{}".format(state.address[12], state.address[13], state.address[14], state.address[15])
        # recordLogin(state.session, state.userid, ip, state.version, state.release, state.os, state.osversion)
        asyncio.create_task(postLogin({
            'session_id': state.session,
            'group_id': state.user_id % math.pow(10, 8),
            'ip': ip,
            'login_time': datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            'version': state.version,
            'release': state.release,
            'os': state.os,
            'osversion': state.osversion
        }))
        return None
    
    def userDisconnected(self, state, current=None):
        # recordLogout(state.session, state.userid)
        asyncio.create_task(postLogout({
            'session_id': state.session,
            'group_id': state.userid % math.pow(10, 8),
            'logout_time': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }))
        return None

    def userStateChanged(self, state, current=None):
        return None
    
    def userTextMessage(self, state, message, current=None):
        return None
    
    def channelCreated(self, state, current=None):
        return None

    def channelRemoved(self, state, current=None):
        return None

    def channelStateChanged(self, state, current=None):
        return None

if __name__ == "__main__":
    # loop = asyncio.get_event_loop()
    asyncio.create_task(pollUserFetch())

    print("Creating callbacks...")
    ice = Ice.initialize(sys.argv)

    meta = Murmur.MetaPrx.checkedCast(ice.stringToProxy('Meta:tcp -h 127.0.0.1 -p 6503'))

    adapter = ice.createObjectAdapterWithEndpoints("Callback.Client", "tcp -h 127.0.0.1")
    adapter.activate()
    
    server = meta.getServer(1)
    
    print("binding to server:")
    print(server)
    print("id:", server.id())
    serverR=Murmur.ServerUpdatingAuthenticatorPrx.uncheckedCast(adapter.addWithUUID(ServerAuthenticatorI(server, adapter)))
    server.setAuthenticator(serverR)
    callbackR = Murmur.ServerCallbackPrx.uncheckedCast(adapter.addWithUUID(ServerCallbackI(server, adapter)))
    server.addCallback(callbackR)

    print('Script running (press CTRL-C to abort)')
    try:
        ice.waitForShutdown()
    except KeyboardInterrupt:
        print('CTRL-C caught, aborting')

    ice.shutdown()
    print("Goodbye")

