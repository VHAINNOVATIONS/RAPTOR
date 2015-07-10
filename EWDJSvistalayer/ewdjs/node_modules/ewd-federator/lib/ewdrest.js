/*

 ----------------------------------------------------------------------------
 | ewd-federator: REST-based Module for federation of EWD.js systems        |
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

*/

var restify = require('restify');
var cp = require('child_process');
var path = require('path');
var events = require('events');
//var client = require('ewdliteclient');
var url = require('url');
var util = require('util');

var EWD = {
  buildNo: 14,
  buildDate: '22 May 2015',
  version: function() {
    return 'ewd-federator: Build ' + this.buildNo + ', ' + this.buildDate;
  },
  startTime: new Date().getTime(),
  started: new Date().toUTCString(),
  elapsedSec: function() {
    var now = new Date().getTime();
    return (now - this.startTime)/1000;
  },
  elapsedTime: function() {
    var sec = this.elapsedSec();
    var hrs = Math.floor(sec / 3600);
    sec %= 3600;
    var mins = Math.floor(sec / 60);
    if (mins < 10) mins = '0' + mins;
    sec = Math.floor(sec % 60);
    if (sec < 10) sec = '0' + sec;
    var days = Math.floor(hrs / 24);
    hrs %= 24;
    return days + ' days ' + hrs + ':' + mins + ':' + sec;
  },
  hSeconds: function() {
    // get current time in seconds, adjusted to Mumps $h time
    var date = new Date();
    var secs = Math.floor(date.getTime()/1000);
    var offset = date.getTimezoneOffset()*60;
    var hSecs = secs - offset + 4070908800;
    return hSecs;
  },
  defaults: function(params) {
    var cwd = params.cwd || process.cwd();
    if (cwd.slice(-1) === '/') cwd = cwd.slice(0,-1);
    var defaults = {
      restPort: 8080,
      childProcess: {
        poolSize: 2,
        path: __dirname + '/ewdrestChildProcess.js',
        auto: true,
        maximum: 4,
        idleLimit: 1800000, // half an hour
        unavailableLimit: 600000, // 10 minutes
        checkInterval: 300000 // 5 minutes
      },
      //childProcessPath: __dirname + '/ewdrestChildProcess.js',
      os: 'linux',
      database: {
        type: 'gtm',
      },
      debug: {
        child_port: 5858,
        web_port: 8089
      },
      ewdGlobalsPath: 'globalsjs',
      modulePath: cwd + '/node_modules',
      //poolSize: 2,
      traceLevel: 3,
      timeout: 120000,
      enableCORS: false,
      management: {
        path: '/_mgr',
        password: 'keepThisSecret!'
      }
    };

    if (params && params.database && typeof params.database.type !== 'undefined') defaults.database.type = params.database.type;
    if (params && typeof params.os !== 'undefined') defaults.os = params.os;
    if (defaults.database.type === 'cache' || defaults.database.type === 'globals') {
      defaults.database = {
        type: 'cache',
        nodePath: 'cache',
        username: '_SYSTEM',
        password: 'SYS',
        namespace: 'USER',
        charset: 'UTF-8',
        lock: 0
      };
      if (defaults.os === 'windows') {
        defaults.database.path = 'c:\\InterSystems\\Cache\\Mgr';
      }
      else {
        defaults.database.path = '/opt/cache/mgr';
      }
    }
    if (defaults.database.type === 'gtm') {
      defaults.database = {
        type: 'gtm',
        nodePath: 'nodem',
      };
    }
    if (defaults.database.type === 'mongodb') {
      defaults.database = {
        type: 'mongodb',
        nodePath: 'mongo',
        address: 'localhost',
        port: 27017
      };
    }
    this.setDefaults(defaults, params);
  },
  setDefaults: function(defaults, params) {
    var name;
    //var value;
    var subDefaults = {
      database: '',
      https: '',
      webSockets: '',
      management: '',
      webRTC: '',
      webservice: ''
    };
    for (name in defaults) {
      if (typeof subDefaults[name] !== 'undefined') {
        this.setPropertyDefaults(name, defaults, params);
      }
      else {
        EWD[name] = defaults[name];
        if (params && typeof params[name] !== 'undefined') EWD[name] = params[name];
      }
    }
    if (EWD.database.type === 'globals') EWD.database.type = 'cache';
    if (params.database && params.database.also) {
      EWD.database.also = params.database.also;
      if (EWD.database.also.length > 0 && EWD.database.also[0] === 'mongodb') {
        EWD.database.address = params.database.address || 'localhost';
        EWD.database.port = params.database.port || 27017;
      }
    }
  },

  setPropertyDefaults: function(property,defaults, params) {
    var name;
    EWD[property] = {};
    for (name in defaults[property]) {
      EWD[property][name] = defaults[property][name];
      if (params && typeof params[property] !== 'undefined') {
        if (typeof params[property][name] !== 'undefined') EWD[property][name] = params[property][name];
      }
    }
  },

  defineDestinations: function(params) {
    var site;
    EWD.destination = {
      _all: {}
    };
    for (site in EWD.server) {
      EWD.destination._all[site] = site;
    }
    if (params.destination) {
      var i;
      for (var name in params.destination) {
        EWD.destination[name] = {};
        for (i = 0; i < params.destination[name].length; i++) {
          site = params.destination[name][i];
          EWD.destination[name][site] = site;
        }
      }
    }
  },

  process: {},
  requestsByProcess: {},
  queue: [],
  queueByPid: {},
  queueEvent: new events.EventEmitter(),
  totalRequests: 0,
  maxQueueLength: 0,

  processClearDown: function() {
    // check for stuck processes and idle ones that can be shut down
    var proc;
    var dur;
    var ok;
    var newPid;
    if (EWD.traceLevel >= 3) console.log('*** checking child process pool at ' + new Date().toUTCString());
    var poolSize = EWD.childProcess.poolSize + 0;
    for (var pid in EWD.process) {
      proc = EWD.process[pid];
      dur = new Date().getTime() - proc.time;
      if (!proc.isAvailable) {
        if (EWD.traceLevel >= 3) console.log('pid: ' + pid + ' not available for ' + dur);
        if (dur > EWD.childProcess.unavailableLimit) {
          // locked for too long - close it down!
          if (poolSize < 2) {
            // start a new child process first!
            newPid = EWD.startChildProcess(99);
            EWD.childProcess.poolSize++;
            if (EWD.traceLevel >= 3) console.log('too few child processes - ' + newPid + ' started');
            poolSize++;
          }
          ok = EWD.stopChildProcess(pid);
          poolSize--;
        }
      }
      else {
        if (EWD.traceLevel >= 3) console.log('pid: ' + pid + ' available for ' + dur);
        if (EWD.childProcess.auto && dur > EWD.childProcess.idleLimit) {
          // idle for too long - close it down unless minimum process pool reached already
          if (poolSize > 1) {
            ok = EWD.stopChildProcess(pid);
            poolSize--;
          }
        }
      }
    }
  },

  processGCEvent: false,

  processGC: function() {
    EWD.processClearDown();
    EWD.processGCEvent = setTimeout(EWD.processGC, EWD.childProcess.checkInterval);
  },

  halt: function() {
    console.log('halt function!');
    //console.log('EWD.process: ' + util.inspect(EWD.process));
    for (var pid in EWD.process) {
      EWD.addToQueue({
        type: 'EWD.exit',
        pid: pid,
      });
    }
  },

  startChildProcesses: function() {
    //console.log("startChildProcesses - poolSize = " + this.poolSize);
    if (this.childProcess.poolSize > 1) {
      var pid;
      for (var i = 1; i < this.childProcess.poolSize; i++) {
        pid = this.startChildProcess(i);
        //console.log('startChildProcess ' + i + '; pid ' + pid);
      }
      pid = null;
      i = null;
    }
  },
  startChildProcess: function(processNo, debug) {
    if (debug) {
      EWD.debug.child_port++;
      process.execArgv.push('--debug=' + EWD.debug.child_port);
    }
    var childProcess = cp.fork(this.childProcess.path, [], {env: process.env});
    var pid = childProcess.pid;
    EWD.process[pid] = childProcess;
    var thisProcess = EWD.process[pid];
    thisProcess.isAvailable = false;
    thisProcess.time =  new Date().getTime();
    thisProcess.started = false;
    this.requestsByProcess[pid] = 0;
    thisProcess.processNo = processNo;
    if (debug) {
      thisProcess.debug = {
        enabled: true,
        port: EWD.debug.child_port,
        web_port: EWD.debug.web_port
      };
    }
    else {
      thisProcess.debug = {enabled: false};
    }
    this.queueByPid[pid] = [];
    thisProcess.on('message', function(response) {
      response.processNo = processNo;
      var release = response.release;
      var pid = response.pid;
      if (EWD.process[pid]) {
        var proc = EWD.process[pid];
        if (response.type !== 'getMemory') {
          if (EWD.traceLevel >= 3) console.log("child process returned response " + JSON.stringify(response));
        }
        if (EWD.childProcessMessageHandlers[response.type]) {
          EWD.childProcessMessageHandlers[response.type](response);
        }
        else {
          if (!response.empty && EWD.traceLevel >= 3) console.log('No handler available for child process message type (' + response.type + ')');
        }
        // release the child process back to the available pool
        //console.log('** response.type: ' + response.type);
        //console.log('** EWD.process for pid ' + pid + ': ' + EWD.process[pid].type);
        if (response.type !== 'EWD.exit' && release) {
          if (EWD.traceLevel >= 3 && response.type !== 'getMemory') console.log('process ' + pid + ' returned to available pool; type=' + response.type);
          delete proc.response;
          delete proc.type;
          delete proc.frontEndService;
          proc.isAvailable = true;
          if (response.type !== 'getMemory') proc.time = new Date().getTime();
          EWD.queueEvent.emit("processQueue");
        }
      }
      response = null;
      pid = null;
      release = null;
    });

    return pid;
  },

  stopChildProcess: function(pid) {
      if (EWD.childProcess.poolSize > 1) {
        if (pid && EWD.process[pid]) {
          var returnValue;
          if (EWD.process[pid].isAvailable) {
            EWD.addToQueue({
              type: 'EWD.exit',
              pid: pid,
            });
            returnValue = {ok: pid + ' flagged to stop'};
          }
          else {
            // process is stuck, so force it down and release its resources
            EWD.process[pid].kill();
            delete EWD.process[pid];
            delete EWD.requestsByProcess[pid];
            delete EWD.queueByPid[pid];
            EWD.childProcess.poolSize--;
            //EWD.sendToMonitor({type: 'workerProcess', action: 'remove', pid: pid});
            if (EWD.traceLevel >= 1) console.log('process ' + pid + " was forced to shut down");
            returnValue = {ok: pid + ' forced closed'};
          }
          return returnValue;
        }
        else {
          return {error: 'Pid not defined or does not exist'};
        }
      }
      else {
        return {error: 'Poolsize must be 1 or greater'};
      }
  },

  returnResponse: function(response, name) {
    var pid = response.pid;
    var process = EWD.process[pid];
    if (process.response) {
      var res = process.response;
      res.header('Content-Type', response.contentType);
      res.send(response[name]);
      delete process.response;
    }
  },

  childProcessMessageHandlers: {
  
    // handlers for explictly-typed messages received from a child process

    childProcessStarted: function(response) {
      EWD.process[response.pid].started = true;
      var requestObj = {
        type:'initialise',
        params: {
          database: EWD.database,
          ewdGlobalsPath: EWD.ewdGlobalsPath,
          traceLevel: EWD.traceLevel,
          startTime: EWD.startTime,
          management: EWD.management,
          no: response.processNo,
          hNow: EWD.hSeconds(),
          modulePath: EWD.modulePath,
          homePath: path.resolve('../'),
          service: EWD.service,
          server: EWD.server,
          timeout: EWD.timeout,
          extensionModule: EWD.extensionModule,
          destination: EWD.destination
        }			
      };
      if (EWD.traceLevel >= 3) console.log("Sending initialise request to " + response.pid + ': ' + JSON.stringify(requestObj, null, 2));
      EWD.process[response.pid].send(requestObj);
    },

    firstChildInitialised: function(response) {
      console.log('First child process started.  Now starting the other processes....');
      EWD.startChildProcesses();
      console.log('Starting REST server...');
      EWD.rest.listen(EWD.restPort, function() {
        console.log('%s listening at %s', EWD.rest.name, EWD.rest.url);
      });
      EWD.queueEvent.on("processQueue", EWD.processQueue);

      process.on( 'SIGINT', function() {
        console.log('*** CTRL & C detected: shutting down gracefully...');
        EWD.halt();
      });

      process.on( 'SIGTERM', function() {
        console.log('*** Master Process ' + process.pid + ' detected SIGTERM signal.  Shutting down gracefully...');
        EWD.halt();
      });

      setTimeout(function() {
        if (EWD.callback) EWD.callback(EWD);
        console.log('******************************************************');
        console.log('*******        ewd-federator is Ready      ***********');
        console.log('******************************************************');
        console.log('  ');
        console.log('Started: ' + EWD.started);
        console.log('Listening on port ' + EWD.restPort);
        console.log('  ');
        EWD.processGC();
      }, 1000);
    },
    restRequest: function(response) {
      var pid = response.pid;
      var process = EWD.process[pid];
      if (process.response) {
        var res = process.response;
        res.header('Content-Type', response.contentType);
        if (response.error) {
          res.send(new restify.RestError({
            statusCode: response.error.statusCode,
            restCode: response.error.restCode || 'InvalidRestRequest',
            message: response.error.message
          }));
        }
        else {
          res.send(response.response);
        }
        delete process.response;
      }
      response = null;
    },

    getConfig: function(response) {
      EWD.returnResponse(response, 'config');
      response = null;
    },

    getGlobalDirectory: function(response) {
      EWD.returnResponse(response, 'globals');
      response = null;
    },

    getGlobalSubscripts: function(response) {
      EWD.returnResponse(response, 'subscripts');
      response = null;
    },

    getMemory: function(response) {
      //console.log('memory; ' + JSON.stringify(response.memory));
      EWD.returnResponse(response, 'memory');
      response = null;
    },

    getServer: function(response) {
      EWD.returnResponse(response, 'server');
      response = null;
    },

    setServer: function(response) {
      response.resp = {ok: true};
      EWD.returnResponse(response, 'resp');
      response = null;
      /*
      var pid = response.pid;
      var process = EWD.process[pid];
      if (process.response) {
        var res = process.response;
        res.header('Content-Type', response.contentType);
        res.send({ok: true});
        delete process.response;
      }
      */
      // update ewdChild.server in each child process from new database record
      for (pid in EWD.process) {
        EWD.addToQueue({
          type: 'updateServer',
          content: {
            name: response.name
          },
          response: res,
          pid: pid
        });
      }
      response = null;
    },

    updateServer: function(response) {
      // no-op
      response = null;
    },

    getSites: function(response) {
      EWD.returnResponse(response, 'sites');
      response = null;
    },

    getDBInfo: function(response) {
      EWD.returnResponse(response, 'database');
      response = null;
    },

    exit: function(response) {
      //console.log("master process received exit response for " + response.pid);
      var pid = response.pid;
      if (EWD.traceLevel >= 1) console.log('process ' + pid + " has been shut down");
      EWD.childProcess.poolSize--;
      delete EWD.process[pid];
      delete EWD.requestsByProcess[pid];
      delete EWD.queueByPid[pid];
      if (EWD.childProcess.poolSize === 0) {
        //if (ewd.statsEvent) clearTimeout(ewd.statsEvent);
        //if (ewd.sessionGCEvent) clearTimeout(ewd.sessionGCEvent);
        setTimeout(function() {
          console.log('ewd-federator is shutting down...');
          process.exit(1);
          // That's it - we're all shut down!
        },1000);
      }
    }

  },

  addToQueue: function(requestObj) {
    if (requestObj.type !== 'webServiceRequest' && requestObj.type !== 'getMemory' && !requestObj.ajax) {
      if (!requestObj.response) {
        if (EWD.traceLevel >= 3) console.log("addToQueue: " + JSON.stringify(requestObj, null, 2));
      }
    }
    // puts a request onto the queue and triggers the queue to be processed
    if (requestObj.pid) {
      //console.log('request ' + requestObj.type + ' added to queue for pid ' + requestObj.pid);
      this.queueByPid[requestObj.pid].push(requestObj);
    }
    else {
      this.queue.push(requestObj);
    }
    this.totalRequests++;
    var qLength = this.queue.length;
    if (qLength > this.maxQueueLength) this.maxQueueLength = qLength;
    if (requestObj.type !== 'webServiceRequest' && requestObj.type !== 'getMemory') {
      if (EWD.traceLevel >= 2) console.log('Request added to Queue: queue length = ' + qLength + '; requestNo = ' + this.totalRequests + '; after ' + this.elapsedTime());
    }
    // trigger the processing of the queue
    this.queueEvent.emit('processQueue');
    requestObj = null;
    qLength = null;
  },

  processQueue: function() {
    var queuedRequest;
    var pid;
    for (pid in EWD.queueByPid) {
      if (EWD.queueByPid[pid].length > 0) {
        if (EWD.process[pid].isAvailable) {
          queuedRequest = EWD.queueByPid[pid].shift();
          EWD.process[pid].isAvailable = false;
          if (queuedRequest.type !== 'getMemory') EWD.process[pid].time = new Date().getTime();
          EWD.sendRequestToChildProcess(queuedRequest, pid);
        }
      }
    }

    pid = (EWD.queue.length !== 0);
    if (pid && EWD.traceLevel >= 3) console.log("processing queue; length " + EWD.queue.length + "; after " + EWD.elapsedTime());
    while (pid) {
      pid = EWD.getChildProcess();
      if (pid) {
        queuedRequest = EWD.queue.shift();
        EWD.sendRequestToChildProcess(queuedRequest, pid);
      }
      if (EWD.queue.length === 0) {
        pid = false;
        if (EWD.traceLevel >= 3) console.log("queue has been emptied");
      }
    }
    queuedRequest = null;
    pid = null;
    if (EWD.queue.length > 0) {
      if (EWD.traceLevel >= 2) console.log("queue processing aborted: no free child proceses available");
      if (EWD.childProcess.auto && EWD.queue[0] && EWD.queue[0].type !== 'getMemory') {
        if (EWD.childProcess.poolSize < EWD.childProcess.maximum) {
          // start new child process
          var newPid = EWD.startChildProcess(99);
          if (EWD.traceLevel >= 3) console.log(newPid + ' started to relieve queue pressure');
          EWD.childProcess.poolSize++;
        }
        else {
          // is everything locked up?
          var proc;
          var dur;
          var poolSize = EWD.childProcess.poolSize + 0;
          var trigger = false;
          for (var pid in EWD.process) {
            proc = EWD.process[pid];
            dur = new Date().getTime() - proc.time;
            if (!proc.isAvailable && poolSize > 1 && dur > EWD.childProcess.unavailableLimit) {
              // get rid of stuck process
              ok = EWD.stopChildProcess(pid);
              poolSize--;
              trigger = true;
            }
          }
          if (trigger) {
            setTimeout(function() {
              // try the queue again - it should now open new processes
              EWD.queueEvent.emit("processQueue");
            },2000);
          }
        }
      }
    }
  },

  getChildProcess: function() {
    var pid;
    // try to find a free child process, otherwise return false
    for (pid in EWD.process) {
      if (EWD.process[pid].isAvailable) {
        EWD.process[pid].isAvailable = false;
        EWD.process[pid].time = new Date().getTime();
        return pid;
      }
    }
    return false;
  },
  sendRequestToChildProcess: function(queuedRequest, pid) {
    var childProcess = EWD.process[pid];
    //console.log('sendRequestToChildProcess = pid = ' + pid);
    var ok;
    if (queuedRequest.type === 'restRequest') {
      childProcess.response = queuedRequest.response;
      ok = EWD.sendToChildProcess({
        type: 'restRequest',
        rest: queuedRequest.rest
      }, pid);
      /*
      childProcess.send({
        type: 'restRequest',
        rest: queuedRequest.rest
      });
      */
      if (ok) EWD.requestsByProcess[pid]++;
      childProcess = null;
      return;
    }
    if (queuedRequest.type === 'exit') {
      ok = EWD.sendToChildProcess({
        type: 'EWD.exit',
      }, pid);
      /*
      childProcess.send({
        type: 'EWD.exit',
      });
      */
      if (ok) EWD.requestsByProcess[pid]++;
      childProcess = null;
      return;
    }
    if (queuedRequest.response) childProcess.response = queuedRequest.response;
    //childProcess.send({
    ok = EWD.sendToChildProcess({
      type: queuedRequest.type,
      content: queuedRequest.content
    }, pid);
    if (ok) EWD.requestsByProcess[pid]++;
    childProcess = null;
  },

  sendToChildProcess: function(obj, pid) {
    try {
      EWD.process[pid].send(obj);
      return true;
    }
    catch(err) {
      //Child process has become unavailable for some reason
      // remove it from the pool and put queuedRequest back on the queue
      EWD.childProcess.poolSize--;
      delete EWD.process[pid];
      delete EWD.requestsByProcess[pid];
      delete EWD.queueByPid[pid];
      EWD.queue.push(queuedRequest);
      // start a new child process
      EWD.startChildProcess(999, false);
      EWD.childProcess.poolSize++;
      return false;
    }
  },

  parser: function(req, res) {
    var method = req.method;
    var site = req.params[0];
    var auth = req.header('Authorization') || '';
    if (auth.indexOf('%2') !== -1) {
      auth = unescape(auth);
      //req.header('Authorization') = auth;
    }
    if (site === '_mgr') {
      EWD.manager(req, res, auth);
      return;
    }
    /*
    if (method === 'GET' && site === '_sites') {
      EWD.addToQueue({
        type: 'getSites',
        response: res
      });
      return;
    }
    */
    var params = {
      url: 'http://' + req.headers.host + req.url,
      params: req.params,
      auth: auth, //req.header('Authorization') || '',
      method: req.method,
      query: req.query,
      header: req.header,
      headers: req.headers
    };
    if (req.body) params.body = JSON.stringify(req.body);

    EWD.addToQueue({
      type: 'restRequest',
      response: res,
      rest: params
    });
  },

  unknownMethodHandler: function(req, res) {
    if (req.method.toLowerCase() === 'options') {
      if (EWD.traceLevel >= 3) console.log('received an options method request');
      var allowHeaders = ['Accept', 'Accept-Version', 'Content-Type', 'Authorization', 'Api-Version', 'Origin', 'X-Requested-With']; // added Origin & X-Requested-With
      if (res.methods.indexOf('OPTIONS') === -1) res.methods.push('OPTIONS');
      res.header('Access-Control-Allow-Credentials', true);
      res.header('Access-Control-Allow-Headers', allowHeaders.join(', '));
      res.header('Access-Control-Allow-Methods', res.methods.join(', '));
      res.header('Access-Control-Allow-Origin', req.headers.origin);
      return res.send(204);
    }
    else  {
      return res.send(new restify.MethodNotAllowedError());
    }
  },

  manager: function(req, res, auth) {
    //if (EWD.traceLevel >= 3) console.log('req.params: ' + JSON.stringify(req.params, null, 2));
    if (req.params['1'].substr(0,3) === 'url') {
      var results = {
        url: 'http://' + req.headers.host + req.url,
        params: req.params,
        auth: auth, //req.header('Authorization') || '',
        method: req.method,
        query: req.query,
        header: req.header,
        headers: req.headers
      };
      if (EWD.traceLevel >= 3) console.log("URL Test: " + JSON.stringify(results, null, 2));
    } 
    //if (req.header('Authorization') === EWD.management.password) {
    if (auth === EWD.management.password) {
      res.header('Content-Type', 'application/json');
      action = req.params[1].split('/')[0];
      if (EWD.managementHandlers[action]) {
        EWD.managementHandlers[action](req, res);
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Action'
        }));
      }
    }
    else {
      res.send(new restify.RestError({
        statusCode: 404,
        restCode: 'InvalidManagementRequest',
        message: 'Missing or Invalid Authorization'
      }));
    }
  },

  managementHandlers: {
    halt: function(req, res) {
      for (var i = 0; i < EWD.childProcess.poolSize; i++) {
        EWD.addToQueue({type: 'exit'});
      }
      res.send({ok: true});
      // see exit child response handler for rest of shutdown logic
    },

    info: function(req, res) {
      if (req.method === 'GET') {
        var pids = [];
        for (var pid in EWD.process) {
          pids.push(pid);
        }
        res.send({
          product: 'ewd-federator',
          version: EWD.version(),
          build: EWD.buildNo,
          nodejs: process.version,
          masterProcess: process.pid,
          poolSize: EWD.childProcess.poolSize,
          childProcesses: pids,
          database: EWD.database,
          started: EWD.started,
          uptime: EWD.elapsedTime()
        });
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Method'
        }));  
      }
    },

    server: function(req, res) {
      if (req.method === 'GET') {
        var name = req.query['name'];
        if (name && name !== '') {
          EWD.addToQueue({
            type: 'getServer',
            content: {
              name: name
            },
            response: res
          });
          // see child Process handler for getServer
        }
        else {
          res.send(new restify.RestError({
            statusCode: 404,
            restCode: 'InvalidManagementRequest',
            message: 'Server Name Not Specified'
          }));  
        }
      }
      else if (req.method === 'PUT') {
        var name = req.query['name'];
        var accessId = req.query['accessId'];
        var host = req.query['host'];
        var port = req.query['port'];
        var secretKey = req.query['secretKey'];
        var ssl = req.query['ssl'];
        var error = '';
        if (!name || name === '') error='Server Name Not Specified';
        if (!accessId || accessId === '') error='accessId Not Specified';
        if (!host || host === '') error='Host Not Specified';
        if (!port || port === '') error='Port Not Specified';
        if (!secretKey || secretKey === '') error='Secret Key Not Specified';
        if (!ssl) error='ssl Invalid or Not Specified';
        if (ssl && ssl !== 'true' && ssl !== 'false') error='ssl Invalid or Not Specified';
        if (error === '') {
          EWD.addToQueue({
            type: 'setServer',
            content: {
              name: name,
              data: {
                accessId: accessId,
                host: host,
                port: port,
                secretKey: secretKey,
                ssl: ssl
              }
            },
            response: res
          });
          // see child Process handler for getServer
        }
        else {
          res.send(new restify.RestError({
            statusCode: 404,
            restCode: 'InvalidManagementRequest',
            message: error
          }));  
        }
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Method'
        }));  
      }
    },

    url: function(req, res) {
      var results = {
        url: 'http://' + req.headers.host + req.url,
        params: req.params,
        auth: req.header('Authorization') || '',
        method: req.method,
        query: req.query,
        header: req.header,
        headers: req.headers
      };
      res.send(results);
      return;
    },


    config: function(req, res) {
      if (req.method === 'GET') {
        EWD.addToQueue({
          type: 'getConfig',
          response: res
        });
        // see child Process handler for getConfig
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Method'
        }));  
      }
    },

    globalDirectory: function(req, res) {
      if (req.method === 'GET') {
        EWD.addToQueue({
          type: 'getGlobalDirectory',
          response: res
        });
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Method'
        }));
      }
    },

    globalSubscripts: function(req, res) {
      //console.log('req = ' + JSON.stringify(req));
      if (req.method === 'GET') {
        EWD.addToQueue({
          type: 'getGlobalSubscripts',
          content: req.params,
          response: res
        });
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Method'
        }));
      }
    },

    internals: function(req, res) {
      var startupParams = {
        restPort: EWD.restPort,
        service: EWD.service,
        server: EWD.server,
        childProcessPath: EWD.childProcess.path,
        os: EWD.os,
        database: EWD.database,
        debug: EWD.debug,
        ewdGlobalsPath: EWD.ewdGlobalsPath,
        modulePath: EWD.modulePath,
        poolSize: EWD.childProcess.poolSize,
        traceLevel: EWD.traceLevel,
        enableCORS: EWD.enableCORS,
        extensionModule: EWD.extensionModule,
        destination: EWD.destination
      };
      var proc;
      var procObj = {};
      for (var pid in EWD.process) {
        proc = EWD.process[pid];
        procObj[pid] = {
          processNo: proc.processNo,
          isAvailable: proc.isAvailable,
          time: proc.time,
          started: proc.started,
          debug: proc.debug
        };
      }
      res.send({
        startupParams: startupParams,
        poolSize: EWD.childProcess.poolSize,
        process: procObj,
        requestsByProcess: EWD.requestsByProcess,
        queueByPid: EWD.queueByPid
      });
    },

    getDBInfo: function(req,res) {
      if (req.method === 'GET') {
        EWD.addToQueue({
          type: 'getDBInfo',
          response: res
        });
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Method'
        }));  
      }
    },

    childProcess: function(req,res) {
      //console.log('*** in ewdrest.js: childProcess - method = ' + req.method);
      if (req.method === 'PUT') {
        // Start a new Child Process
        var debug = req.params.debug || false;
        //console.log('debug: ' + debug);
        var pid = EWD.startChildProcess(999, debug);
        if (EWD.traceLevel >= 3) console.log('**** new child process started: ' + pid);
        EWD.childProcess.poolSize++;
        res.send({
          pid: pid
        });
        return;
        /*
        var messageObj = {};
        messageObj.pid = pid;
        if (debug) {
          messageObj.debug = EWD.process[pid].debug;
          messageObj.debug.web_port = EWD.debug.web_port;
        }
        */
      }
      else if (req.method === 'DELETE') {
        if (EWD.childProcess.poolSize > 1) {
          //console.log('DELETE - req.params = ' + JSON.stringify(req.params));
          var pid = req.params.pid;
          if (pid && EWD.process[pid]) {
            if (EWD.process[pid].isAvailable) {
              EWD.addToQueue({
                type: 'EWD.exit',
                pid: pid,
              });
            }
            else {
              // process is stuck, so force it down and release its resources
              EWD.process[pid].kill();
              delete EWD.process[pid];
              delete EWD.requestsByProcess[pid];
              delete EWD.queueByPid[pid];
              EWD.childProcess.poolSize--;
              //ewd.sendToMonitor({type: 'workerProcess', action: 'remove', pid: pid});
              //if (ewd.traceLevel >= 1) ewd.log('process ' + pid + " was forced to shut down", 1);
            }
          }
        }
        res.send({ok: true});
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Method'
        }));  
      }
    },

    sites: function(req, res) {
      if (req.method === 'GET') {
        EWD.addToQueue({
          type: 'getSites',
          response: res
        });
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Method'
        }));  
      }
    },

    memory: function(req, res) {
      if (req.method === 'GET') {
        var pid = req.query['pid'];
        if (!pid) {
          res.send(new restify.RestError({
            statusCode: 404,
            restCode: 'InvalidManagementRequest',
            message: 'Missing or Invalid Pid'
          }));     
        }
        else {
          if (pid.toString() === process.pid.toString()) {
            // get memory for master process
            var cp = {};
            for (var cpPid in EWD.process) {
              cp[cpPid] = {
                available: EWD.process[cpPid].isAvailable,
                requests: EWD.requestsByProcess[cpPid]
              };
            }
            var mem = process.memoryUsage();
            var memory = {
              rss: (mem.rss /1024 /1024).toFixed(2),
              heapTotal: (mem.heapTotal /1024 /1024).toFixed(2), 
              heapUsed: (mem.heapUsed /1024 /1024).toFixed(2),
              queueLength: EWD.queue.length,
              childProcesses: cp,
              upTime: EWD.elapsedTime()
            };
            res.send(memory);
            return;
          }
          if (EWD.process[pid]) {
            // child process memory request, so put on queue for specified pid
            EWD.addToQueue({
              type: 'getMemory',
              response: res,
              pid: pid
            });
            return;
          }
          //console.log(JSON.stringify(req.query));
          res.send({ok: false});
        }
      }
      else {
        res.send(new restify.RestError({
          statusCode: 404,
          restCode: 'InvalidManagementRequest',
          message: 'Invalid Method'
        }));  
      }
    }
  }

};



module.exports = {

  start: function(params, callback) {
    EWD.defaults(params);
    EWD.server = params.server;
    EWD.service = params.service;
    EWD.defineDestinations(params);
    EWD.extensionModule = params.extensionModule || '';
    EWD.rest = restify.createServer(params.restServer);
    EWD.rest.use(restify.acceptParser(EWD.rest.acceptable));
    EWD.rest.use(restify.bodyParser());
    EWD.rest.use(restify.queryParser());

    if (params.enableCORS) {
      EWD.rest.use(restify.CORS());
      EWD.rest.use(restify.fullResponse());
    }

    EWD.callback = callback;
	
    console.log('   ');
    console.log('******************************************************');
    console.log('**** ewd-federator: Build ' + EWD.buildNo + ' (' + EWD.buildDate + ') *******');
    console.log('******************************************************');
    console.log('  ');
    console.log('Started: ' + EWD.started);
    console.log('Master process: ' + process.pid);
    console.log(EWD.childProcess.poolSize + ' child Node processes will be started...');

    EWD.rest.get(/^\/([a-zA-Z0-9_\.~-]+)\/(.*)/, EWD.parser);
    EWD.rest.post(/^\/([a-zA-Z0-9_\.~-]+)\/(.*)/, EWD.parser);
    EWD.rest.put(/^\/([a-zA-Z0-9_\.~-]+)\/(.*)/, EWD.parser);
    EWD.rest.del(/^\/([a-zA-Z0-9_\.~-]+)\/(.*)/, EWD.parser);

    EWD.rest.on('MethodNotAllowed', EWD.unknownMethodHandler);
    
    // start child processes which, in turn, starts Restify Listener
    EWD.startChildProcess(0);
  }
};
