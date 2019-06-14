const server_addr = 'https://seat.eve-info.net'
const token = 'SjG1IWcBk6LKwmRJliJJlnXguL3IUKEq'

const express = require('express')
const cache = require('memory-cache')
const axios = require('axios')
const bodyparser = require('body-parser')

const app = express()

let users = {}

app.use(bodyparser.json())

app.post('/mumble-connector/api/authenticate', async (req, res) => {
    try {
        const c = cache.get(req.body.name.toString())
        if (!c) {
            if (users[req.body.name.toString()]) {
                const user = users[req.body.name.toString()]
                if (user.pw === req.body.pw) {
                    return res.json({
                        result: parseInt(req.body.name),
                        newname: user.name,
                        groups: user.groups
                    })
                } else {
                    return res.json({ result: -1 })
                }
            } else {
                fetchLogin(req.body.name, req.body.pw)
                return res.json({ result: -3 })
            }
        } else {
            cache.del(req.body.name.toString())
            const a = JSON.parse(c)
            return res.json(a)
        }
    } catch (e) {
        console.error(e)
    }
})

app.post('/mumble-connector/api/login', async (req, res) => {
    postLogin(req.body)
    return res.json({ok: true})
})

app.post('/mumble-connector/api/logout', async (req, res) => {
    postLogout(req.body)
    return res.json({ ok: true })
})

app.listen(18001, '127.0.0.1')

async function fetchLogin(name, pw) {
    const rq = await axios({
        method: 'POST',
        url: server_addr + '/mumble-connector/api/authenticate',
        headers: {
            'X-Token': token
        },
        data: {
            name, pw
        },
        validateStatus: null
    })
    if (rq.status === 200) {
        cache.put(name.toString(), JSON.stringify(rq.data), 60000)
        return true
    } else {
        return false
    }
}

async function postLogin(data) {
    return axios({
        method: 'POST',
        url: server_addr + '/mumble-connector/api/login',
        headers: {
            'X-Token': token
        },
        data,
        validateStatus: null
    })
}

async function postLogout(data) {
    return axios({
        method: 'POST',
        url: server_addr + '/mumble-connector/api/logout',
        headers: {
            'X-Token': token
        },
        data,
        validateStatus: null
    })
}

async function fetchAllUserData(data) {
    const rq = await axios({
        method: 'POST',
        url: server_addr + '/mumble-connector/api/data',
        headers: {
            'X-Token': token
        },
        validateStatus: null
    })
    if (rq.status === 200) {
        users = rq.data
        console.log('Loaded users data.')
        return true
    } else {
        return false
    }
}

setInterval(fetchAllUserData, 30000)
fetchAllUserData()
