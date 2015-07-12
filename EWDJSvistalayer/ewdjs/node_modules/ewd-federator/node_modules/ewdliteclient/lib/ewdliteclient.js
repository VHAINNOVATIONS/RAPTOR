/*
 ----------------------------------------------------------------------------
 | ewdliteclient: Web Service Client interface for accessing EWD.js systems |
 |                                                                          |
 | Copyright (c) 2014-15 M/Gateway Developments Ltd,                        |
 | Reigate, Surrey UK.                                                      |
 | All rights reserved.                                                     |
 |                                                                          |
 | http://www.mgateway.com                                                  |
 | Email: rtweed@mgateway.com                                               |
 |                                                                          |
 |                                                                          |
 | Licensed under the Apache License, Version 2.0 (the "License");          |
 | you may not use this file except in compliance with the License.         |
 | You may obtain a copy of the License at                                  |
 |                                                                          |
 |     http://www.apache.org/licenses/LICENSE-2.0                           |
 |                                                                          |
 | Unless required by applicable law or agreed to in writing, software      |
 | distributed under the License is distributed on an "AS IS" BASIS,        |
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. |
 | See the License for the specific language governing permissions and      |
 |  limitations under the License.                                          |
 ----------------------------------------------------------------------------

  Build 10; 20 March 2015

*/

var crypto = require('crypto');
var https = require('https');
var http = require('http');
var querystring = require('querystring');

var utils = {
  escape: function(string, encode) {
    if (encode === "escape") {
      var unreserved = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.~';
      var escString = '';
      var c;
      var hex;
      for (var i=0; i< string.length; i++) {
        c = string.charAt(i);
        if (unreserved.indexOf(c) !== -1) {
          escString = escString + c;
        }
        else {
          hex = string.charCodeAt(i).toString(16).toUpperCase();
          //console.log(string + "; c=" + c + "; hex = " + hex);
          if (hex.length === 1) hex = '0' + hex;
          escString = escString + '%' + hex;
        }
      }
      return escString;
    }
    else {
      var enc = encodeURIComponent(string);
      return enc.replace(/\*/g, "%2A").replace(/\'/g, "%27").replace(/\!/g, "%21").replace(/\(/g, "%28").replace(/\)/g, "%29");
    }
  },
  createStringToSign: function(action, includePort, encodeType) {
    var stringToSign;
    var name;
    var amp = '';
    var value;
    var keys = [];
    var index = 0;
    var pieces;
    var host = action.host;
    if (!includePort) { 
      if (host.indexOf(":") !== -1) {
        pieces = host.split(":");
        host = pieces[0];
      }
    }
    var url = action.uri;
    var method = 'GET'
    stringToSign = method + '\n' + host + '\n' + url + '\n';
    for (name in action.query) {
      if (name !== 'signature') {
        keys[index] = name;
        index++;
      }
    }
    keys.sort();
    for (var i=0; i < keys.length; i++) {
      name = keys[i];
      value = action.query[name];
      //console.log("name = " + name + "; value = " + value);
      stringToSign = stringToSign + amp + utils.escape(name, encodeType) + '=' + utils.escape(value, encodeType);
      amp = '&';
    }
    return stringToSign;
  },
  digest: function(string, key, type) {
    // type = sha1|sha256|sha512
    var hmac = crypto.createHmac(type, key.toString());
    hmac.update(string);
    return hmac.digest('base64');
  }

};

module.exports = {

  run: function(obj, callback) {
    //console.log('ewdliteclient: obj = ' + JSON.stringify(obj, null, 2));
    var params = obj.params;
    var secretKey = obj.secretKey;
    var appName = obj.appName;
    var serviceName = obj.serviceName;
    var method = obj.method || 'GET';
    var post_data;
    var amp;
    var name;
    var timeout = obj.timeout || 120000;
    if (method === 'POST' || method === 'PUT') {
      post_data = querystring.stringify(obj.post_data);
    }
    var path = '/' + appName + '/' + serviceName;
    if (typeof obj.ewdjs === 'undefined') obj.ewdjs = true;
    if (obj.ewdjs) {
      path = '/json/' + appName + '/' + serviceName;
    }
    else {
      path = '/' + obj.params.rest_path;
      amp = '?';
      for (name in obj.params) {
        if (name !== 'accessId' && name.indexOf('rest_') === -1) {
          path = path + amp + name + '=' + obj.params[name];
          amp = '&';
        }
      }
    }
    var options = {
      hostname: obj.host,
      port: obj.port,
      method: method,
      path: path,
      agent: false,
      rejectUnauthorized: false // set this to true if remote site uses proper SSL certs
    };

    if (obj.ewdjs && secretKey) {
      var uri = options.path;
      params.timestamp = new Date().toUTCString();
      var amp = '?';
      for (name in params) {
        options.path = options.path + amp + utils.escape(name, 'uri') + '=' + utils.escape(params[name], 'uri');
        amp = '&';
      } 
      var action = {
        host: options.hostname,
        query: params,
        uri: uri
      };
      var stringToSign = utils.createStringToSign(action, false, "uri");
      var hash = utils.digest(stringToSign, secretKey, 'sha256');
      options.path = options.path + '&signature=' + utils.escape(hash, 'uri');
    }
    var req;

    //console.log('ewdliteclient: options: ' + JSON.stringify(options, null, 2));

    if (obj.ssl) {
      // breakout point
      if (obj.returnUrl) {
        return 'https://' + options.hostname + ':' + options.port + options.path;
      }
      req = https.request(options, function(response) {
        var data = '';
        response.on('data', function(chunk) {
          data += chunk;
        });
        response.on('end', function() {
          if (callback) {
            try {
              if (response.statusCode !== 200) {
                json = JSON.parse(data);
                json.statusCode = response.statusCode; 
                callback(json, '');
              }
              else {
                callback(false, JSON.parse(data));
              }
            }
            catch(err) {
              console.log("ewdliteclient error: " + err + "; data = " + data);
              callback(err + '; data = ' + data);
            }
          }
          return;
        });
      });
    }
    else {
      if (obj.returnUrl) {
        return 'http://' + options.hostname + ':' + options.port + options.path;
      }
      req = http.request(options, function(response) {
        var data = '';
        var json;
        response.on('data', function(chunk) {
          data += chunk;
        });
        response.on('end', function() {
          if (callback) {
            try {
              if (response.statusCode !== 200) {
                json = JSON.parse(data);
                json.statusCode = response.statusCode; 
                callback(json, '');
              }
              else {
                callback(false, JSON.parse(data));
              }
            }
            catch(err) {
              console.log("ewdliteclient error: " + err + "; data = " + data);
              callback(err + '; data = ' + data);
            }
          }
          return;
        });
      });
    }

    req.on('error', function(error) {
      if (callback && !req.timedOut) {
        try {
          callback(JSON.parse(error));
        }
        catch (err) {
          console.log("ewdliteclient error: " + err + "; returned error = " + error);
          callback(err + '; returned error = ' + error);
        }
      }
    });
    req.setTimeout(timeout, function() {
      req.end();
      req.connection.destroy();
      req.timedOut = true;
      if (callback) callback('serverTimeout');
    });
    if (method === 'POST') req.write(post_data);
    req.end();
    return;
  },

  example: function(sessid) {

    // modify the values below as appropriate for the
    // remote EWD Lite system you wish to access

    var args = {
      host: '192.168.1.98',
      port: 8080,
      ssl: true,
      appName: 'demo',
      serviceName: 'webServiceExample',
      params: {
        // query string name/value pairs
        accessId: 'rob',  // required by EWD Lite's security
        sessid: sessid          // EWD.js Session Id (required by demo/webServiceExample)
      },
      secretKey: 'a1234567'  // %zewd("EWDLiteServiceAccessId", accessId) = secretKey 
                             //  must exist on the remote system and match the values here.
                             //  Used to sign the outgoing request
    };

   this.run(args, function(error, data) {
     // do whatever you need to do with the returned JSON data, eg:
     results = {};
     if (error) {
       // note: use of console.log will upset testing in REPL
       //console.log('An error occurred: ' + JSON.stringify(error));
       results.error = error;
     }
     else {
       //console.log('Data returned by web service: ' + JSON.stringify(data));
       results.data = data;
     }
   });

  }

};

