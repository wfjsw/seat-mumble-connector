# mumble auth script via api sync
# written by arrai & bogie
# built on the default api.eveonline.com python script
# expects password from client: userid@apikey
# done:
#
# downloading + checking API for character data
# groups, acls
#
# todo:
#
# optimize download(threading?)
# ???
# profit
#
#
#Copyright (C) 2009 bogie,arrai
#
#This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License 
#as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
#
#This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
#or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
#
#You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>.


import httplib, urllib
import Ice, sys
import json
import socket

from datetime import datetime
# Ice.loadSlice('Murmur.ice')
import Murmur

TOKEN = "SjG1IWcBk6LKwmRJliJJlnXguL3IUKEq"

def authenticate(groupid, password):
    file = "/mumble-connector/api/authenticate"
    headers = {
        "Content-type": "application/json",
        "Accept": "application/json",
        "X-Token": TOKEN
    }
    params = json.dumps({
        "name": groupid,
        "pw": password
    })
    conn = httplib.HTTPSConnection("seat.eve-info.net")
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

def recordLogin(sessionid, groupid, ip, version, release, os, osversion):
    file = "/mumble-connector/api/login"
    headers = {
        "Content-type": "application/json",
        "Accept": "application/json",
        "X-Token": TOKEN
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
    conn = httplib.HTTPSConnection("seat.eve-info.net")
    conn.request("POST", file, params, headers)
    response = conn.getresponse()
    if response.status != 200:
        print response.status
        conn.close
        return None
    else:
        conn.close
        return None

def recordLogout(sessionid, groupid):
    file = "/mumble-connector/api/logout"
    headers = {
        "Content-type": "application/json",
        "Accept": "application/json",
        "X-Token": TOKEN
    }
    params = json.dumps({
        "session_id": sessionid,
        "group_id": groupid,
        "logout_time": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
    })
    conn = httplib.HTTPSConnection("seat.eve-info.net")
    conn.request("POST", file, params, headers)
    response = conn.getresponse()
    if response.status != 200:
        print response.status
        conn.close
        return None
    else:
        conn.close
        return None


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

