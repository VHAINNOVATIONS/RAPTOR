/*
 ----------------------------------------------------------------------------
 | ewdrestChildProcess: Child Worker Process for EWD REST Server            |
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

  Build 10; 22 May 2015

*/

var fs = require('fs');
var os = require('os');
var events = require('events');
var client = require('ewdliteclient');
var util = require('util');

var mumps;
var database;
var mongo;
var mongoDB;

var htmlEscape = function(text) {
  return text.toString().replace(/&/g, '&amp;').
    replace(/</g, '&lt;').  // it's not neccessary to escape >
    replace(/"/g, '&quot;').
    replace(/'/g, '&#039;');
};

// This set of utility functions will be made available via ewd.util

var EWD = {
  hSeconds: function(date) {
    // get [current] time in seconds, adjusted to Mumps $h time
    if (date) {
      date = new Date(date);
    }
    else {
      date = new Date();
    }
    var secs = Math.floor(date.getTime()/1000);
    var offset = date.getTimezoneOffset()*60;
    var hSecs = secs - offset + 4070908800;
    return hSecs;
  },
  hDate: function(date) {
    var hSecs = EWD.hSeconds(date);
    var days = Math.floor(hSecs / 86400);
    var secs = hSecs % 86400;
    return days + ',' + secs;
  },
  getDateFromhSeconds: function(hSecs) {
    var sec = hSecs - 4070908800;
    return new Date(sec * 1000).toString();
  },
  getMemory: function() {
    var mem = process.memoryUsage();
    var memory = {
      rss: (mem.rss /1024 /1024).toFixed(2),
      heapTotal: (mem.heapTotal /1024 /1024).toFixed(2), 
      heapUsed: (mem.heapUsed /1024 /1024).toFixed(2)
    };
    memory.pid = process.pid;
    memory.modules = ewdChild.module;
    return memory;
  },
  createToken: function() {
    var result = [];
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    var tokenLength = 63;
    while (--tokenLength) {
      result.push(chars.charAt(Math.floor(Math.random() * chars.length)));
    }
    return result.join('');
  },
  createEventList: function() {
    ewdChild.extend.eventList = {};
    for (var i = 0; i < ewdChild.extend.events.length; i++) {
      ewdChild.extend.eventList[ewdChild.extend.events[i]] = true;
    }
  },

  requireAndWatch: function(path, moduleName, isExtensionModule) {
    var module = false;
    try {
      module = require(path);
      if (moduleName) ewdChild.module[moduleName] = module;
      if (module && module.services && moduleName) {
        var list = module.services();
        ewdChild.services[moduleName] = {};
        var services = ewdChild.services[moduleName];
        for (var i = 0; i < list.length; i++) {
          services[list[i]] = {};
        }
      }
      if (ewdChild.traceLevel >= 3) ewdChild.log("requireAndWatch: " + path + " loaded by process " + process.pid, 3);
      fs.watch(path, function(event, filename) {
        if (ewdChild.traceLevel >= 3) ewdChild.log(filename + ' has changed - event = ' + event + '; process: ' + process.pid, 3);
        if (event === 'change') {
          try {
            var path = require.resolve(filename);
            //console.log("require reload for filename " + filename + "; path = " + path);
            delete require.cache[path];
            var module = require(path);
            if (moduleName) ewdChild.module[moduleName] = module;
            if (isExtensionModule) {
              ewdChild.extend = module;
              EWD.createEventList();
            }
            if (module && module.services && moduleName) ewdChild.services[moduleName] = module.services();
            if (!module) console.log('require failed');
            if (ewdChild.traceLevel >= 3) ewdChild.log(filename + " reloaded successfully", 3);
          }
          catch (err) {
            if (ewdChild.traceLevel >= 3) ewdChild.log(path + " could not be reloaded: " + JSON.stringify(err), 3);
          }
        }
      });
    }
    catch(err) {
      if (ewdChild.traceLevel >= 2) ewdChild.log("Error in requireAndWatch - " + path + " could not be loaded", 2);
    }
    return module;
  },

  sendRequest: function(destination, params) {
    /*
      params = {
        type: 'rob/ping',
        path: 'robServices/parse',
        method: 'GET',
        contentType: 'application.json',
        nvps: {
          a: 123,
          b: 234
        },
        //optionally if you want different nvp values for each site:
        nvpsBySite: {
          reigate: {
            a: 111,
            b: 222
          },
          guildford: {
            a: 222,
            b: 3333
          }
        }
      }
    */

    function clearDown() {
      destination = null;
      params = null;
      site = null;
    }
   
    var site;
    //console.log('sendRequest: destination = ' + destination);
    //console.log(' params = ' + JSON.stringify(params, null, 2));
    if (destination !== null && typeof destination === 'object') {
      params.multiple = {
        destination: destination,
        max: 0
      }
      for (site in destination) {
        params.multiple.max++;
        //console.log('max incremented');
      }
      ewdChild.counter[params.type] = 0;
      ewdChild.aggregateResponse[params.type] = {};
      for (site in destination) {
        params.site = site;
        ewdChild.sendToSite(params);
      }
      clearDown();
      return;
    }
    if (ewdChild.destination[destination]) {
      params.multiple = {
        destination: destination,
        max: 0
      }
      for (site in ewdChild.destination[destination]) {
        params.multiple.max++;
      }
      ewdChild.counter[params.type] = 0;
      ewdChild.aggregateResponse[params.type] = {};
      for (site in ewdChild.destination[destination]) {
        params.site = site;
        ewdChild.sendToSite(params);
      }
      clearDown();
      return;
    }
    params.site = destination;
    ewdChild.sendToSite(params);
    clearDown();
  },

  returnRESTResponse: function(response, contentType) {
    contentType = contentType || 'application/json';
    process.send({
      pid: process.pid,
      type: 'restRequest',
      error: false,
      response: response,
      release: true,
      contentType: contentType
    });
  },

  returnRESTError: function(error, contentType) {
    // error = {
    //   statusCode: xxx, // eg 401
    //   restCode: xxxx,  // eg 'InvalidRequest'
    //   message: xxxxx  // eg 'Value of x was undefined'
    // }
    if (!error.statusCode) error.statusCode = '404';
    contentType = contentType || 'application/json';
    process.send({
      pid: process.pid,
      type: 'restRequest',
      error: error,
      release: true,
      contentType: contentType
    });
  }

};

var ewdChild = {

  log: function(message, level, clearLog) {
    if (+level <= +ewdChild.traceLevel) {
      console.log(message);
    }
    message = null;
    level = null;
  },

  counter: {},
  aggregateResponse: {},
  isEmpty: function(obj) {
    for (var name in obj) {
      return false;
    }
    return true;
  },

  responseEvent: new events.EventEmitter(),

  module: {},
  services: {},
  getModulePath: function(application) {
    var path = ewdChild.modulePath;
    var lchar = path.slice(-1);
    if (lchar === '/' || lchar === '\\') {
      path = path.slice(0,-1);
    }
    var delim = '/';
    if (process.platform === 'win32') delim = '\\';
    path = path + delim + application + '.js';
    return path;
  },

  invokeClient: function(request, args) {
    //console.log('*** invokeClient');
    //console.log('request: ' + JSON.stringify(request, null, 2));
    //console.log('args: ' + JSON.stringify(args, null, 2));
    (function(request, args) {
      client.run(args, function(error, data) {
        
        function clearDown() {
          error = null;
          data = null;
          statusCode = null;
          message = null;
          responseObj = null;
          restCode = null;
        }

        var statusCode;
        var message;
        var responseObj;
        var restCode;
        //console.log('*************** client response received!');
        if (error) {
          //console.log('error: ' + JSON.stringify(error));
          statusCode = 400;
          message = 'Unknown error';
          restCode = 'RESTError';
          if (error.code && error.message) {
            statusCode = error.statusCode || error.message.statusCode || 400;
            restCode = error.code;
            message = error.message;
          }
          else if (error.error) {
            if (!error.error.statusCode) {
              statusCode = 400;
              message = error.error;
            }
            else {
              statusCode = error.error.statusCode;
              message = error.error.text || error.error.message;
              restCode = error.error.code || restCode;
            }
          }
          if (message === 'Unknown error') {
            console.log('Unknown error occurred: ' + JSON.stringify(error));
            if (error === 'serverTimeout') {
              statusCode = 408;
              message = 'Remote Server Timed Out';
            }
          }
          responseObj = {
            type: 'error',
            error: {
              statusCode: statusCode,
              restCode: restCode,
              message: message
            },
            contentType: request.contentType
          };
          ewdChild.responseEvent.emit('processResponse', responseObj);
        }
        else {
          responseObj = {
            type: request.type,
            site: request.site,
            error: false,
            response: data,
            contentType: request.contentType
          };
          if (request.multiple) responseObj.multiple = request.multiple;
          ewdChild.responseEvent.emit('processResponse', responseObj);
        }
        clearDown();
      });
    }(request, args));
    request = null;
    args = null;
  },

  sendToSite: function(params) {

    function clearDown() {
      params = null;
      destinationObj = null;
      contentType = null;
      pathArr = null;
      args = null;
      request = null;
      responseObj = null;
      ewdjs = null;
      nvps = null;
    }

    var pathArr;
    var args;
    var request;
    var responseObj;
    var ewdjs;
    var nvps;
    //console.log('$#$#$#$#$# sendToSite - params = ' + JSON.stringify(params, null, 2));
    var destinationObj = ewdChild.server[params.site];
    var contentType = params.contentType || 'application/json';
    if (typeof destinationObj !== 'undefined') {
      pathArr = params.path.split('/');
      ewdjs = destinationObj.ewdjs;
      nvps = params.nvps;
      if (params.nvpsBySite && params.nvpsBySite[params.site]) nvps = params.nvpsBySite[params.site];
      if (!nvps.rest_path) nvps.rest_path = params.path; // if manually sent request
      if (typeof destinationObj.ewdjs === 'undefined') ewdjs = true;
      args = {
        host: destinationObj.host,
        port: destinationObj.port,
        ssl: destinationObj.ssl,
        appName: pathArr[0],
        ewdjs: ewdjs,
        serviceName: pathArr[1],
        params: nvps,
        secretKey: destinationObj.secretKey
      };
      if (ewdChild.timeout) args.timeout = ewdChild.timeout;
      if (destinationObj.accessId) {
        if (!args.params) args.params = {};
        args.params.accessId = destinationObj.accessId;
      }

      // thanks to Ward deBacker for the fix/enhancement here:

      args.method = params.method || 'GET';
      if (args.method === 'POST' || args.method === 'PUT') {
        args.post_data = params.post_data;
      }
      request = {
        site: params.site,
        type: params.type,
        contentType: contentType
      };
      if (params.multiple) request.multiple = params.multiple;
      ewdChild.invokeClient(request, args);
    }
    else {
      responseObj = {
        type: 'error',
        error: {
          statusCode: 404,
          restCode: 'InvalidRemoteServer',
          message: 'Invalid Remote Server Specified'
        },
        contentType: contentType
      };
      ewdChild.responseEvent.emit('processResponse', responseObj);
    }
    clearDown();
  },

  messageHandlers: {

    // handlers for incoming messages, by type

    initialise: function(messageObj) {
      var params = messageObj.params;
      // initialising this worker process
      if (ewdChild.traceLevel >= 3) ewdChild.log(process.pid + " initialise: params = " + JSON.stringify(params), 3);
      ewdChild.ewdGlobalsPath = params.ewdGlobalsPath;
      ewdChild.startTime = params.startTime;
      ewdChild.database = params.database;
      ewdChild.traceLevel = params.traceLevel;
      ewdChild.homePath = params.homePath;
      var hNow = params.hNow;
      ewdChild.modulePath = params.modulePath;
      ewdChild.server = params.server;
      ewdChild.service = params.service;
      ewdChild.timeout = params.timeout;
      ewdChild.extensionModule = params.extensionModule;
      ewdChild.destination = params.destination;
      mumps = require(ewdChild.ewdGlobalsPath);
      if (ewdChild.extensionModule !== '') {
        try {
          var path = ewdChild.getModulePath(ewdChild.extensionModule);
          ewdChild.extend = EWD.requireAndWatch(path, null, true);
          EWD.createEventList();
        }
        catch(err) {
          console.log('*** Unable to load extension module ' + ewdChild.extensionModule);
        }
      }
      if (ewdChild.database.type === 'mongodb') ewdChild.database.nodePath = 'mongoGlobals';
      var globals;
      try {
        globals = require(ewdChild.database.nodePath);
      }
      catch(err) {
        console.log("**** ERROR: The database gateway module " + ewdChild.database.nodePath + ".node could not be found or loaded");
        console.log(err);
        process.send({
          pid: process.pid, 
          type: 'firstChildInitialisationError'
        });
        return;
      }
      var dbStatus;
      if (ewdChild.database.type === 'cache') {
        database = new globals.Cache();
        dbStatus = database.open(ewdChild.database);
        if (dbStatus.ErrorMessage) {
          console.log("*** ERROR: Database could not be opened: " + dbStatus.ErrorMessage);
          if (dbStatus.ErrorMessage.indexOf('unexpected error') !== -1) {
            console.log('It may be due to file privileges - try starting using sudo');
          }
          else if (dbStatus.ErrorMessage.indexOf('Access Denied') !== -1) {
            console.log('It may be because the Callin Interface Service has not been activated');
            console.log('Check the System Management Portal: System - Security Management - Services - %Service Callin');
          }
          process.send({
            pid: process.pid, 
            type: 'firstChildInitialisationError'
          });
          return;
        }
      }
      else if (ewdChild.database.type === 'gtm') {
        database = new globals.Gtm();
        dbStatus = database.open();
        if (dbStatus && dbStatus.ok !== 1) console.log("**** dbStatus: " + JSON.stringify(dbStatus));
        ewdChild.database.namespace = '';
        var node = {global: '%zewd', subscripts: ['nextSessid']}; 
        var test = database.get(node);
        if (test.ok === 0) {
          console.log('*** ERROR: Global access test failed: Code ' + test.errorCode + '; ' + test.errorMessage);
          if (test.errorMessage.indexOf('GTMCI') !== -1) {
            console.log('***');
            console.log('*** Did you start EWD.js using "node ewdStart-gtm gtm-config"? ***');
            console.log('***');
          } 
          process.send({
            pid: process.pid, 
            type: 'firstChildInitialisationError'
          });
          return;
        }
      }
      else if (ewdChild.database.type === 'mongodb') {
        mongo = require('mongo');
        mongoDB = new mongo.Mongo();
        database = new globals.Mongo();
        dbStatus = database.open(mongoDB, {address: ewdChild.database.address, port: ewdChild.database.port});
        ewdChild.database.namespace = '';
      }
      if (ewdChild.database.also && ewdChild.database.also.length > 0) {
        if (ewdChild.database.also[0] === 'mongodb') {
          mongo = require('mongo');
          mongoDB = new mongo.Mongo();
          mongoDB.open({address: ewdChild.database.address, port: ewdChild.database.port});
        }
      }
      mumps.init(database);

      // ********************** Load Global Indexer *******************
      try {
        var path = ewdChild.getModulePath('globalIndexer');
        var indexer = EWD.requireAndWatch(path);
        indexer.start(mumps);
        if (ewdChild.traceLevel >= 2) ewdChild.log("** Global Indexer loaded: " + path, 2);
      }
      catch(err) {}
      // ********************************************************
  
      var zewd = new mumps.GlobalNode('zewd', ['ewdjs', ewdChild.httpPort]);
      
      if (params.no === 0) {
        // first child process that is started clears down persistent stored EWD.js data
        ewdChild.log("First child process (' + process.pid + ') initialising database...");
        //var funcObj;
        //var resultObj;
        var pczewd = new mumps.Global('%zewd');
        pczewd.$('relink')._delete();
        pczewd = null;
  
        zewd._delete();

        // **** Synchronise server and services between database and config file ******

        zewd = new mumps.GlobalNode('zewdREST', []);
        var zewdServer = zewd.$('server');
        var zewdServers = zewdServer._getDocument();
        var zewdService = zewd.$('service');
        var zewdServices = zewdService._getDocument;
        var name;
        for (name in ewdChild.server) {
          if (!zewdServers[name]) {
            zewdServer.$(name)._setDocument(ewdChild.server[name]);
          }
        }
        for (name in zewdServers) {
          if (!ewdChild.server[name]) {
            ewdChild.server[name] = zewdServer.$(name)._getDocument();
          }
        }
        for (name in ewdChild.service) {
          if (!zewdServices[name]) {
            zewdService.$(name)._setDocument(ewdChild.service[name]);
          }
        }
        for (name in zewdServices) {
          if (!ewdChild.service[name]) {
            ewdChild.service[name] = zewdService.$(name)._getDocument();
          }
        }

        process.send({
          pid: process.pid, 
          type: 'firstChildInitialised',
          release: true
        });
      }
      //var mem = EWD.getMemory();
      //console.log('memory: ' + JSON.stringify(mem, null, 2));
      //zewd.$('processes').$(process.pid)._value = EWD.getDateFromhSeconds(hNow);
      //console.log('hNow set for ' + process.pid + ': ' + hNow);
      zewd = null;
      process.send({
        pid: process.pid,
        type: 'initialise',
        release: true,
        empty: true
      });
    },
    //  ** end of initialise function

    'EWD.exit': function(messageObj) {
      console.log('child process ' + process.pid + ' signalled to exit');
      setTimeout(function() {
        process.exit(1);
      },500);
      process.send({
        pid: process.pid, 
        type: 'exit'
      });
    },

    getConfig: function(messageObj) {
      process.send({
        type: 'getConfig',
        pid: process.pid,
        config: {
          server: ewdChild.server,
          service: ewdChild.service
        },
        contentType: 'application/json',
        release: true
      });
    },

    getDBInfo: function(messageObj) {
      process.send({
        type: 'getDBInfo',
        pid: process.pid,
        database: database.version(),
        contentType: 'application/json',
        release: true
      });
    },

    restRequest: function(messageObj) {

      function clearDown() {
        rest = null;
        contentType = null;
        destination = null;
        destinationObj = null;
        service = null;
        serviceObj = null;
        messageObj = null;
        path = null;
        site = null;
        nvps = null;
        name = null;
        params = null;
        result = null;
        ewdObj = null;
        type = null;
      }

      var path;
      var site;
      var nvps;
      var name;
      var params;
      var result;
      var ewdObj;
      var type;
      var rest = messageObj.rest;
      var service;
      var serviceObj;
      var destination = rest.params[0];
      var destinationObj = ewdChild.server[destination];
      if (destinationObj && !destinationObj.ewdjs) {
        //path = '/' + rest.params[1];
        path = rest.params[1];
        contentType = 'application/json';
      }
      else {
        service = rest.params[1].split('/')[0];
        serviceObj = ewdChild.service[service];
        if (typeof serviceObj === 'undefined') {
          process.send({
            pid: process.pid,
            type: 'restRequest',
            error: {
              statusCode: 404,
              restCode: 'InvalidRESTService',
              message: 'Invalid REST Service Specified'
            },
            release: true,
            contentType: 'application/json'
          });
          clearDown();
          return;
        }
        path = serviceObj.module + '/' + serviceObj.service;
        contentType = serviceObj.contentType || 'application/json';
      }
      nvps = {
        rest_url: rest.url,
        rest_path: rest.params[1],
        rest_auth: rest.auth,
        rest_method: rest.method,
        rest_site: site
      };
      for (name in rest.query) {
        nvps[name] = rest.params[name];
      }
      var post_data = {};
      for (name in rest.params) {
        if (name !== '0' && name !== '1' && !rest.query[name]) {
          post_data[name] = rest.params[name];
        }
      }
      type = rest.params[1];
      if (ewdChild.extend && ewdChild.extend.pathToType && ewdChild.extend.pathToType[type]) type = ewdChild.extend.pathToType[type];
      params = {
        type: type,
        path: path,
        method: rest.method,
        contentType: contentType,
        nvps: nvps,
        post_data: post_data,
        headers: rest.headers
      };
      result = {
        sendRequest: true
      };
      params.destination = destination;
      if (ewdChild.extend && (ewdChild.extend.eventList) && ewdChild.extend.eventList['request']) {
        //console.log('****** extend eventList: ' + JSON.stringify(ewdChild.extend.eventList));
        ewdObj = {
          mumps: mumps,
          db: database,
          util: EWD,
          server: ewdChild.server,
          service: ewdChild.service,
          destinations: ewdChild.destination
        };
        //console.log('emitting request event');
        ewdChild.extend.event.emit('request', params, ewdObj);
      }
      else {
        EWD.sendRequest(destination, params);
      }
      clearDown();
    },

    getMemory: function(messageObj) {
      messageObj = null;
      process.send({
        type: 'getMemory',
        pid: process.pid,
        memory: EWD.getMemory(),
        contentType: 'application/json',
        release: true
      });
    },

    getServer: function(messageObj) {
      var name = messageObj.content.name;
      var server = new mumps.GlobalNode('zewdREST', ['server', name]);
      var serverObj = {};
      serverObj[name] = server._getDocument();
      process.send({
        type: 'getServer',
        pid: process.pid,
        server: serverObj,
        contentType: 'application/json',
        release: true
      });
      server = null;
      messageObj = null;
      serverObj = null;
    },

    setServer: function(messageObj) {
      var content = messageObj.content;
      var server = new mumps.GlobalNode('zewdREST', ['server', content.name]);
      server._setDocument(content.data);
      process.send({
        type: 'setServer',
        pid: process.pid,
        name: content.name,
        release: true
      });
      server = null;
      messageObj = null;
    },

    updateServer: function(messageObj) {
      var name = messageObj.content.name;
      var server = new mumps.GlobalNode('zewdREST', ['server', name]);
      ewdChild.server[name] = server._getDocument();
      process.send({
        type: 'updateServer',
        pid: process.pid,
        ok: true,
        release: true
      });
      server = null;
      messageObj = null;
    },

    getSites: function(messageObj) {
      messageObj = null;
      var sites = [];
      for (var name in ewdChild.server) {
        sites.push(name);
      }
      process.send({
        type: 'getSites',
        pid: process.pid,
        sites: sites,
        contentType: 'application/json',
        release: true
      });
    },

    getGlobalDirectory: function(messageObj) {
      var gloArray = mumps.getGlobalDirectory();
      var data = [];
      var rec;
      for (var i = 0; i < gloArray.length; i++) {
        rec = {
          name: gloArray[i],
          type: 'folder',
          subscripts: [],
          globalName: gloArray[i],
          operation: 'db'
        };
        data.push(rec);
      }
      process.send({
        type: 'getGlobalDirectory',
        pid: process.pid,
        globals: data,
        contentType: 'application/json',
        release: true
      });
    },

    getGlobalSubscripts: function(messageObj) {
      var subscriptArr = JSON.parse(messageObj.content.subscripts);
      var glo = new mumps.GlobalNode(messageObj.content.globalName, subscriptArr);
      var data = {
        operation: messageObj.content.operation,
        globalName: messageObj.content.globalName,
        rootLevel: messageObj.content.rootLevel,
        subscripts: []
      };
      var rec;
      var count = 0;
      var type;
      glo._forEach(function(subscript, subNode) {
        count++;
        if (count > 200) return true;
        if (subNode._hasValue) {
          type = 'folder';
          if (!subNode._hasProperties) type = 'item';
          rec = {name: htmlEscape(subscript) + '<span>: </span>' + htmlEscape(subNode._value), type: type};
        }
        else {
          rec = {name: subscript, type: 'folder'};
        }
        rec.subscripts = subscriptArr.slice(0);
        rec.subscripts.push(subscript);
        rec.operation = messageObj.content.operation;
        rec.globalName = messageObj.content.globalName;
        data.subscripts.push(rec);
      });
      process.send({
        type: 'getGlobalSubscripts',
        pid: process.pid,
        subscripts: data,
        contentType: 'application/json',
        release: true
      });
    },

    'EWD.resetPassword': function(messageObj) {
      var zewd = new mumps.GlobalNode('zewd', ['ewdjs', ewdChild.httpPort]);
      zewd.$('management').$('password')._value = messageObj.password;
      var sessions = new mumps.GlobalNode('%zewdSession',['session']);
      sessions._forEach(function(sessid, session) {
        session.$('ewd_password')._value = messageObj.password;
      });
      return {ok: true};
    },

    'EWD.setParameter': function(messageObj) {
      if (messageObj.name === 'monitorLevel') {
        ewdChild.traceLevel = messageObj.value;
      }
      if (messageObj.name === 'logTo') {
        ewdChild.logTo = messageObj.value;
      }
      if (messageObj.name === 'changeLogFile') {
        ewdChild.logFile = messageObj.value;
      }
      return {ok: true};
    },

    exit: function(messageObj) {
      console.log("*** child process " + process.pid + "; exit - setTimeout about to fire");
      setTimeout(function() {
        process.exit(1);
      },500);
      return {type: 'exit'};
    },

  },

  responseHandlers: function(responseObj) {
    //console.log('*** responseHandlers: type = ' + responseObj.type);
    //console.log('*** responseHandlers: responseObj: ' + JSON.stringify(responseObj, null, 2));
    //console.log('counter: ' + JSON.stringify(ewdChild.counter));
    //console.log('aggregateResponse: ' + JSON.stringify(ewdChild.aggregateResponse));
    var type = responseObj.type;
    var completed = true;
    var params;

    function clearDown() {
      type = null;
      completed = null;
      responseObj = null;
      params = null;
    }

    if (responseObj.multiple) {
      ewdChild.counter[type]++;
      if (ewdChild.counter[type] < responseObj.multiple.max) {
        completed = false;
      }
    }
    if (type && type !== '') {
      if (type === 'error') {
        if (!responseObj.multiple) {
          EWD.returnRESTError(responseObj.error, responseObj.contentType);
          clearDown();
          return;
        }
        else {
          ewdChild.aggregateResponse[type][responseObj.site] = {
            error: responseObj.error
          };
          if (completed) {
            EWD.returnRESTResponse(ewdChild.aggregateResponse[type], responseObj.contentType);
            clearDown();
            return;
          }
        }
      }
      if (responseObj.multiple && responseObj.response) {
        ewdChild.aggregateResponse[type][responseObj.site] = responseObj.response;
        if (completed) {
          responseObj.response = ewdChild.aggregateResponse[type];
        }
      }
      /*
      if (completed && ewdChild.extend && ewdChild.extend.onResponse && ewdChild.extend.onResponse[type]) {
        params = {
          sendRequest: ewdChild.sendRequest,
          mumps: mumps,
          db: database,
          util: EWD,
          server: ewdChild.server,
          service: ewdChild.service,
          destinations: ewdChild.destination
        };
        ewdChild.extend.onResponse[type](responseObj, params);
        clearDown();
        return;
      }
      */
      if (completed && ewdChild.extend && ewdChild.extend.eventList && ewdChild.extend.eventList[type]) {
        params = {
          sendRequest: ewdChild.sendRequest,
          mumps: mumps,
          db: database,
          util: EWD,
          server: ewdChild.server,
          service: ewdChild.service,
          destinations: ewdChild.destination
        };
        //ewdChild.extend.onResponse[type](responseObj, params);
        ewdChild.extend.event.emit(type, responseObj, params);
        clearDown();
        return;
      }
      if (completed && responseObj.response) {
        // simple unprocessed automated response
        EWD.returnRESTResponse(responseObj.response, responseObj.contentType);
        clearDown();
        return;
      }
      if (!completed) {
        clearDown();
        return;
      }
      else {
        EWD.returnRESTError({
          statusCode: 404,
          restCode: 'UndefinedResponsePayload',
          message: 'Internal error: REST Response Payload Undefined or Missing'
        }, responseObj.contentType);
      }
    }
    else {
      EWD.returnRESTError({
        statusCode: 404,
        restCode: 'UndefinedResponseType',
        message: 'Internal error: REST Response Type Undefined or Missing'
      }, responseObj.contentType);
    }
  }

};

// Handle incoming messages

process.on('message', function(messageObj) {
  if (ewdChild.traceLevel >= 3 && messageObj.type !== 'getMemory') ewdChild.log('child process ' + process.pid + ' received message:' + JSON.stringify(messageObj, null, 2), 3);
  ewdChild.counter = {};
  ewdChild.aggregateResponse = {};
  var type = messageObj.type;
  var params;
  if (ewdChild.messageHandlers[type]) {
    ewdChild.messageHandlers[type](messageObj);
  }
  else if (ewdChild.extend && ewdChild.extend.messageHandlers && ewdChild.extend.messageHandlers[type]) {
    params = {
      mumps: mumps,
      db: database,
      util: EWD,
      server: ewdChild.server,
      service: ewdChild.service
    }
    ewdChild.extend.messageHandlers[type](messageObj, params);
  }
  else {
    process.send({
      type: 'error',
      error: 'Message type (' + type + ') not recognised',
      pid: process.pid,
      release: true
    });
  }
  messageObj = null;
  params = null;
  type = null;
});

// Child process shutdown handler - close down database cleanly

process.on('exit',function() {
  if (database) {
    try {
      database.close();
    }
    catch(err) {}
  }
  if (ewdChild.traceLevel >= 2) ewdChild.log('*** ' + process.pid + ' closed ' + ewdChild.database.type, 2);
  if (ewdChild.database && ewdChild.database.also && ewdChild.database.also.length > 0) {
    if (ewdChild.database.also[0] === 'mongodb') {
      if (mongoDB) mongoDB.close();
    }
  }
});

process.on( 'SIGINT', function() {
  console.log('Child Process ' + process.pid + ' detected SIGINT (Ctrl-C) - ignored');
});
process.on( 'SIGTERM', function() {
  console.log('Child Process ' + process.pid + ' detected SIGTERM signal - ignored');
});

// Generic responseEvent handler

ewdChild.responseEvent.on('processResponse', ewdChild.responseHandlers);

// OK ready to go!

console.log('Child process ' + process.pid + ' has started');

// kick off the initialisation process now that the Child Process has started

process.send({
  type: 'childProcessStarted', 
  pid: process.pid
});
