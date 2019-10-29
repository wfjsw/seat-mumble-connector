import httplib, urllib
import Ice, sys
import json
import socket

from datetime import datetime
# Ice.loadSlice('Murmur.ice')
import Murmur

pswd = {}

# TOKEN = "SjG1IWcBk6LKwmRJliJJlnXguL3IUKEq"

async def authenticate(groupid, password):
    file = "/mumble-connector/api/authenticate"
    headers = {
        "Content-type": "application/json",
        "Accept": "application/json",
        # "X-Token": TOKEN
    }
    params = json.dumps({
        "name": groupid,
        "pw": password
    })
    conn = httplib.HTTPConnection("127.0.0.1", 18001)
    conn.request("POST", file, params, headers)
    response = conn.getresponse()
    if response.status != 200:
        print response.status
        print response.read()
        conn.close
        return -1, None, None
    else:
        data = response.read()
        sdata = json.loads(data)
        print sdata
        conn.close
        if sdata.get('result') < 0:
            return sdata.get('result'), None, None
        else:
            return sdata.get('result'), sdata.get('newname'), sdata.get('groups')

async def recordLogin(sessionid, groupid, ip, version, release, os, osversion):
    file = "/mumble-connector/api/login"
    headers = {
        "Content-type": "application/json",
        "Accept": "application/json",
        # "X-Token": TOKEN
    }
    params = json.dumps({
        "session_id": sessionid,
        "group_id": groupid,
        "ip": ip,
        "login_time": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "version": version,
        "release": release,
        "os": os,
        "osversion": osversion
    })
    conn = httplib.HTTPConnection("127.0.0.1", 18001)
    conn.request("POST", file, params, headers)
    response = conn.getresponse()
    if response.status != 200:
        print response.status
        conn.close
        return None
    else:
        conn.close
        return None

async def recordLogout(sessionid, groupid):
    file = "/mumble-connector/api/logout"
    headers = {
        "Content-type": "application/json",
        "Accept": "application/json",
        # "X-Token": TOKEN
    }
    params = json.dumps({
        "session_id": sessionid,
        "group_id": groupid,
        "logout_time": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
    })
    conn = httplib.HTTPConnection("127.0.0.1", 18001)
    conn.request("POST", file, params, headers)
    response = conn.getresponse()
    if response.status != 200:
        print response.status
        conn.close
        return None
    else:
        conn.close
        return None

async def getUserList():


###
# mumble auth part
###

class ServerAuthenticatorI(Murmur.ServerUpdatingAuthenticator):
    global server
    def __init__(self, server, adapter):
        self.server = server

    def authenticate(self, name, pw, certificates, certhash, cerstrong, out_newname):
        charid, newname, groups = authenticate(name, pw)
        return (charid, newname, groups)

    def createChannel(name, server, id):
        return -2
        
    def registerPlayer(self, name, current=None):
      print "Someone tried to register " + name
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
        recordLogin(state.session, state.userid, ip, state.version, state.release, state.os, state.osversion)
        return None
    
    def userDisconnected(self, state, current=None):
        recordLogout(state.session, state.userid)
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
    print "Creating callbacks..."
    ice = Ice.initialize(sys.argv)

    meta = Murmur.MetaPrx.checkedCast(ice.stringToProxy('Meta:tcp -h 127.0.0.1 -p 6503'))

    adapter = ice.createObjectAdapterWithEndpoints("Callback.Client", "tcp -h 127.0.0.1")
    adapter.activate()
    
    server = meta.getServer(1)
    
    print "binding to server:"
    print server
    print "id:", server.id()
    serverR=Murmur.ServerUpdatingAuthenticatorPrx.uncheckedCast(adapter.addWithUUID(ServerAuthenticatorI(server, adapter)))
    server.setAuthenticator(serverR)
    callbackR = Murmur.ServerCallbackPrx.uncheckedCast(adapter.addWithUUID(ServerCallbackI(server, adapter)))
    server.addCallback(callbackR)

    print 'Script running (press CTRL-C to abort)';
    try:
        ice.waitForShutdown()
    except KeyboardInterrupt:
        print 'CTRL-C caught, aborting'

    ice.shutdown()
    print "Goodbye"


# setup the parameters we will be sending to the webserver; note that all of this
# information is gathered from the API Key page that the user should visit, and
# the characterID is gathered from /account/Characters.xml.aspx

