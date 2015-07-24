/*

 ----------------------------------------------------------------------------
 | globals.js: Node.js JavaScript/JSON projection of Mumps Globals          |
 |                                                                          |
 | Copyright (c) 2013-15 M/Gateway Developments Ltd,                        |
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

  Build 30; 14 July 2015

  Thanks to David Wicksell for bug fix to fast getDocument() function

*/

var events = require("events");
var changeHandler = new events.EventEmitter();

/*
changeHandler.on('aftersave', function(node) {
  //console.log('*** saved ' + node.global + ' ' + JSON.stringify(node.subscripts) + ': ' + node.data);
  if (ewd.onAfterSave) ewd.onAfterSave(node);
});
*/

var ewd = {};

var init = function(db) {
  ewd.mumps = db;
};

var deleteGlobal = function(globalName) {
  changeHandler.emit('beforeGlobalDelete', globalName);
  new Global(globalName)._delete();
  changeHandler.emit('afterGlobalDelete', globalName);
};

var version = function() {
  return ewd.mumps.version();
};

var fn = function(funcName) {
  var args = [];
  var i;
  if (arguments.length > 1) {
    for (i = 1; i < arguments.length; i++) {
      args.push(arguments[i]);
    }
  }
  if (ewd.mumps.version().indexOf('GT.M') !== -1) {
    // check for relinking
    // equivalent of i $g(^%zewd("relink"))=1,'$d(^%zewd("relink","process",$j)) s ok=$$relink^%zewdGTMRuntime()
    var relink = new GlobalNode('%zewd', ['relink']);
    if (relink._value === 1) {
      if (!relink.$('process').$(process.pid)._exists) {
        var ok = ewd.mumps.function({
          function: 'relink^%zewdGTMRuntime'
        });
      }
    }
  }
  var result;
  try {
    result = ewd.mumps.function({function: funcName, arguments: args}).result;
  }
  catch(err) {
    return '-1^' + err;
  }
  return result;
};

var MumpsFn = function(funcName) {
  this.execute = function() {
    var args = [];
    var i;
    for (i = 0; i < arguments.length; i++) {
      args.push(arguments[i]);
    }
    var result;
    try {
      result = ewd.mumps.function({function: funcName, arguments: args}).result;
    }
    catch(err) {
      return '-1^' + err;
    }
    return result;
  };
  this.run = this.execute;
};

var getGlobalDirectory = function(range) {
  if (typeof range === 'undefined') range = {};
  return ewd.mumps.global_directory(range);
};

var deleteGlobalNode = function(globalName, subscripts) {
  new GlobalNode(globalName, subscripts)._delete();
};

var Global = function(globalName) {
  return new GlobalNode(globalName,[]);
};

var GlobalNode = function(globalName, subscripts) {
  this._node = {global: globalName, subscripts: subscripts};
  // this.subscripts returns a clone of the subscripts array
  this._subscripts = subscripts.slice(0);
  this._globalName = globalName;
};

// Now define all its instance methods

var proto = GlobalNode.prototype;

Object.defineProperty(proto, '_defined', {
  enumerable: true,
  configurable: false,
  get: function() {
    var def = ewd.mumps.data(this._node).defined;
    if (typeof def === 'undefined') return 0;
    return def;
  }
});

Object.defineProperty(proto, '_exists', {
  enumerable: true,
  configurable: false,
  get: function() {
    var def = this._defined;
    return def !== 0;
  }
});

Object.defineProperty(proto, '_hasValue', {
  enumerable: true,
  configurable: false,
  get: function() {
    return ((this._defined === 1)||(this._defined === 11));
  }
});

Object.defineProperty(proto, '_hasProperties', {
  enumerable: true,
  configurable: false,
  get: function() {
    return ((this._defined === 10)||(this._defined === 11));
  }
});

proto._keys = Object.keys(proto).slice(0);

Object.defineProperty(proto, '_reservedNames', {
  enumerable: false,
  configurable: false,
  get: function() {
    var i;
    var names = {};
    for (i = 0; i < this._keys.length; i++) {
      names[this._keys[i]] = '';
    }
    return names;
  }
});


Object.defineProperty(proto, '_value', {
  enumerable: true,
  configurable: false,
  get: function() {
    var value = ewd.mumps.get(this._node).data;
    if (value === 'true') value = true;
    if (value === 'false') value = false;
    return value;
  },
  set: function(value) {
    var node = this._node;
    var oldValue = ewd.mumps.get(node).data;
    node.data = value;
    changeHandler.emit('beforesave', node);
    ewd.mumps.set(node);
    node.oldValue = oldValue;
    node.newValue = value;
    changeHandler.emit('aftersave', node);
  }
});

proto._getValue = function() {
  return this._value;
};

proto._setValue = function(value) {
  this._value = value;
};

proto._increment = function() {
  changeHandler.emit('beforesave', this._node);
  var node = this._node;
  var oldValue = ewd.mumps.get(node).data;
  var data = ewd.mumps.increment(this._node).data;
  node.data = data;
  node.newValue = data;
  node.oldValue = oldValue;
  changeHandler.emit('aftersave', node);
  return data;
};

proto._delete = function() {
  changeHandler.emit('beforedelete', this._node);
  var node = this._node;
  var oldValue = ewd.mumps.get(node).data;
  ewd.mumps.kill(node);
  node.oldValue = oldValue;
  changeHandler.emit('afterdelete', node);
};

proto._property = function(subscript) {
  // don't overwrite a global node's preset properties or methods
  if (subscript in this._reservedNames) return false;
  var subs = this._subscripts.slice(0);
  subs.push(subscript);
  if (typeof this[subscript] === 'undefined') this[subscript] = new GlobalNode(this._globalName,subs);
  return this[subscript];
};

proto.$ = proto._property;
proto._getProperty = proto._property;

proto._getProperties = function() {
  var properties = [];
  this._forEach(function(key, subNode, node) {
    properties.push(key);
    node.$(key);
  });
  return properties;
};

Object.defineProperty(proto, '_properties', {
  enumerable: false,
  configurable: false,
  get: function() {
    var properties = [];
    var subs = this._subscripts.slice(0);
    subs.push('');
    var node = {global: this._globalName, subscripts: subs};
    do {
      node = ewd.mumps.order(node);
      if (node.result !== '') properties.push(node.result);
    }
    while (node.result !== '');
    return properties;
  }
});

proto._setPropertyValue = function(subscript, value) {
  var subs = this._subscripts.slice(0);
  subs.push(subscript);
  var node = {global: this._globalName, subscripts: subs, data: value};
  ewd.mumps.set(node);
};

proto._fixProperties = function() {
  var findProperties = function(globalNode) {
    globalNode._forEach(function(key,subNode, node) {
      if (subNode._hasProperties) {
        findProperties(node.$(key));
      }
    });
    return globalNode;
  };
  return findProperties(this);
};

proto._forEach = function(callback) {
  // to iterate in reverse: 
  //  globalNode._forEach({direction: 'reverse'}, function(index) {
  //    console.log("index: " + index);
  //  });

  var result;
  var gnode;
  var direction = 'forwards';
  if (arguments.length > 1) {
    if (arguments[0].direction === 'reverse') direction = 'reverse';
    callback = arguments[1]; 
  }
  var subs = this._subscripts.slice(0);
  subs.push('');
  var node = {global: this._globalName, subscripts: subs};
  var quit = false;
  do {
    if (direction === 'forwards') {
      node = ewd.mumps.order(node);
    }
    else {
      node = ewd.mumps.previous(node);
    }
    result = node.result;
    if (result !== '') {
      gnode = this.$(result);
      quit = callback(result, gnode, this);
      if (quit) break;
    }
  }
  while (result !== '');
};

proto._forRange = function(fromSubscript, toSubscript, callback) {
  // to iterate in reverse: 
  // globalNode._forRange({from: 'r', to: 'd', direction: 'reverse'}, function(index) {
  //   console.log("index: " + index);
  // });

  var end = '';
  var result;
  var gnode;
  var subs;
  var node;
  var seed;
  var quit;
  var direction = 'forwards';
  if (arguments.length === 2) {
    var args = arguments[0];
    callback = arguments[1]; 
    if (args.direction === 'reverse') direction = 'reverse';
    fromSubscript = args.from;
    toSubscript = args.to;
  }
  if (direction === 'forwards') {
    if (toSubscript !== '') {
      subs = this._subscripts.slice(0);
      subs.push(toSubscript);
      node = {global: this._globalName, subscripts: subs};
      end = ewd.mumps.order(node).result;
    }
    subs = this._subscripts.slice(0);
    subs.push(fromSubscript);
    node = {global: this._globalName, subscripts: subs};
    seed = ewd.mumps.previous(node).result;
    quit = false;
    do {
      node = ewd.mumps.order(node);
      result = node.result;
      if (result !== end) {
        gnode = this.$(result);
        quit = callback(result, gnode, this);
        if (quit) break;
      }
    }
    while (result !== end);
  }
  else {
    if (toSubscript !== '') {
      subs = this._subscripts.slice(0);
      subs.push(toSubscript);
      node = {global: this._globalName, subscripts: subs};
      end = ewd.mumps.previous(node).result;
    }
    subs = this._subscripts.slice(0);
    subs.push(fromSubscript);
    node = {global: this._globalName, subscripts: subs};
    seed = ewd.mumps.next(node).result;
    quit = false;
    do {
      node = ewd.mumps.previous(node);
      result = node.result;
      if (result !== end) {
        gnode = this.$(result);
        quit = callback(result, gnode, this);
        if (quit) break;
      }
    }
    while (result !== end);
  }
};

proto._forPrefix = function(prefx, callback) {
  // to iterate in reverse: 
  // globalNode._forPrefix({prefix: 've', direction: 'reverse'}, function(index) {
  //   console.log("index: " + index);
  // });

  var result;
  var gnode;
  var subs;
  var node;
  var seed;
  var quit;
  var direction = 'forwards';
  if (typeof arguments[0] !== 'string') {
    var args = arguments[0];
    if (args.direction === 'reverse') direction = 'reverse';
    prefx = args.prefix;
  }
  if (direction === 'forwards') {
    if (prefx === '') return;
    subs = this._subscripts.slice(0);
    subs.push(prefx);
    node = {global: this._globalName, subscripts: subs};
    node = ewd.mumps.previous(node);
    seed = node.result;
    subs = this._subscripts.slice(0);
    subs.push(seed);
    node = {global: this._globalName, subscripts: subs};
    quit = false;
    do {
      node = ewd.mumps.order(node);
      result = node.result;
      if (result !== '') {
        //if (result.indexOf(prefx) === -1) break;
        if (result.substr(0, prefx.length) !== prefx) break;
        gnode = this.$(result);
        quit = callback(result, gnode, this);
        if (quit) break;
      }
    }
    while (result !== '');
  }
  else {
    if (prefx === '') return;
    subs = this._subscripts.slice(0);
    subs.push(prefx);
    node = {global: this._globalName, subscripts: subs};
    do {
      node = ewd.mumps.order(node);
      result = node.result;
      if (result !== '') {
        //if (result.indexOf(prefx) === -1) break;
        if (result.substr(0, prefx.length) !== prefx) break;
      }
    }
    while (result !== '');
    seed = node.result;
    subs = this._subscripts.slice(0);
    subs.push(seed);
    node = {global: this._globalName, subscripts: subs};
    quit = false;
    do {
      node = ewd.mumps.previous(node);
      result = node.result;
      if (result !== '') {
        //if (result.indexOf(prefx) === -1) break;
        if (result.substr(0, prefx.length) !== prefx) break;
        gnode = this.$(result);
        quit = callback(result, gnode, this);
        if (quit) break;
      }
    }
    while (result !== '');
  }
};

proto._forEachSubNode = function(callback) {
  // iterates physical node by node - in Mumps terms using $QUERY

  var isWithinRoot = function(signature, currentArray) {
    // has it moved beyond the root Global Node that originally seeded the loop?  If so, stop the loop

    var match = true;
    for (var i = 0; i < signature.length; i++) {
      if (signature[i].toString() !== currentArray[i].toString()) {
        match = false;
        break;
      }
    }
    return match;
  };

  var node = this._node;
  var ok = true;
  var quit;
  var subscripts = this._subscripts;
  do {
    node = ewd.mumps.next_node(node);
    if (node.defined !== 0) {
      if (isWithinRoot(subscripts, node.subscripts)) {
        quit = callback(node.data, node, this);
        if (quit === false) ok = false;
      }
      else {
        ok = false;
      }
    }
    else {
      ok = false;
    }
  } while (ok);
};

proto._count = function() {
  var count = 0;
  this._forEach(function(key) {
    count++;
  });
  return count;
}; 

proto._getParent = function() {
  var subs = this._subscripts.slice(0);
  if (subs.length > 0) {
    subs.pop();
    return new GlobalNode(this._globalName, subs);
  }
  else {
    return;
  }
};

Object.defineProperty(proto, '_parent', {
  enumerable: false,
  configurable: false,
  get: function() {
    return this._getParent();
  }
});

proto._getNextProperty = function(seed) {
    var subs = this._subscripts.slice(0);
    subs.push(seed);
    var node = {global: this._globalName, subscripts: subs};
    return ewd.mumps.order(node).result;
  };

proto._getPreviousProperty = function(seed) {
  var subs = this._subscripts.slice(0);
  subs.push(seed);
  var node = {global: this._globalName, subscripts: subs};
  return ewd.mumps.previous(node).result;
};

proto._next = proto._getNextProperty;
proto._previous = proto._getPreviousProperty;

Object.defineProperty(proto, '_firstProperty', {
  enumerable: false,
  configurable: false,
  get: function() {
    return this._getNextProperty('');
  }
});

Object.defineProperty(proto, '_lastProperty', {
  enumerable: false,
  configurable: false,
  get: function() {
    return this._getPreviousProperty('');
  }
});

Object.defineProperty(proto, '_first', {
  enumerable: false,
  configurable: false,
  get: function() {
    return this._getNextProperty('');
  }
});

Object.defineProperty(proto, '_last', {
  enumerable: false,
  configurable: false,
  get: function() {
    return this._getPreviousProperty('');
  }
});

proto._getDocument = function(base, useArrays) {

  if (!base) base = 0;
  if (typeof useArrays === 'undefined') useArrays = true;
  var globalName = this._globalName;

  var arrayOfSubscripts = function(globalNode) {
    var expected = base;
    var isArray = true;
    var subs = globalNode.subscripts.slice(0);
    subs.push("");
    var node = {global: globalName, subscripts: subs};
    var result;  
    do {
      node = ewd.mumps.order(node);
      result = node.result;
      if (result !== '') {
        if (+result !== expected) {
          isArray = false;
          break;
        } 
        else {
          expected++;
        }
      }
    }
    while (result !== '');
    return isArray;
  };

  var getSubnodes = function(globalNode) {
    var isArray = false;
    if (useArrays) isArray = arrayOfSubscripts(globalNode);
    var document;
    if (isArray) {
      document = [];
    }
    else {
      document = {};
    }
    var result;
    var subs = globalNode.subscripts.slice(0);
    subs.push('');
    var defined;
    var node = {global: globalName, subscripts: subs};
    var index;
    do {
      node = ewd.mumps.order(node);
      result = node.result;
      if (result !== '') {
        index = result;
        if (isArray) index = index - base;
        defined = ewd.mumps.data(node).defined;
        if (defined === 1 || defined === 11) {
          document[index] = ewd.mumps.get(node).data;
          if (document[index] === 'true') document[index] = true;
          if (document[index] === 'false') document[index] = false;
        }
        if (defined === 10 || defined === 11) {
          document[index] = getSubnodes(node);
        }
      }
    }
    while (result !== '');
    return document;
  };

  var fastGetDocument = function(node) {
    var noOfSubscripts = node.subscripts.length;

    var addToJSON = function(obj, subscripts, value) {

      var append = function(obj, subscript) {
        // Fix courtesy of David Wicksell, Fourthwatch Software. 
        //  To cater for Global nodes with both data and child subscripts, change:
        //if (typeof obj[subscript] === 'undefined') {
        // to:

        if (typeof obj[subscript] !== 'object' || typeof obj[subscript] === 'undefined') {

        // end of fix

	   obj[subscript] = {};
	 }
        return obj[subscript];
      };
  
      var build = function(obj) {
        i++;
        if (i === subscripts.length) {
	   obj = value;
	   return obj;
        }
        var obj2 = append(obj, subscripts[i]);
        obj[subscripts[i]] = build(obj2);
        return obj;
      };

      var i = -1;
      obj = build(obj);
      return obj;
    };

    var isSubNode = function(signature, currentArray) {
      var match = true;
      for (var i = 0; i < signature.length; i++) {
        if (signature[i].toString() !== currentArray[i].toString()) {
          match = false;
          break;
        }
      }
      return match;
    };

    var document = {};
    var signature = node.subscripts;
    var match = true;
    var subsCopy;
    do {
      node = ewd.mumps.next_node(node);
      match = false;
      if (node.defined !== 0) match = isSubNode(signature, node.subscripts);
      if (match) {
        subsCopy = node.subscripts.slice(0);
        subsCopy.splice(0, noOfSubscripts);
        document = addToJSON(document, subsCopy, node.data);
      }  
    } while (match);
    return document;
  };

  if (!useArrays) {
    if (ewd.mumps.next_node(this._node).ok) {
      return fastGetDocument(this._node);
    }
    else {
      return getSubnodes(this._node);
    }
  }
  else {
    return getSubnodes(this._node);
  }
};

proto._setDocument = function(document, fast, offset) {

  var globalName = this._globalName;

  if (!offset) offset = 0;

  var fixNumericString = function(value) {
    if (value === true || value === false) return value.toString();
    return value;
  };

  var setFast = function(obj, globalNode) {
    var subs;
    var i;
    var j;
    var value;
    for (i in obj){
      if (obj[i] === null) obj[i] = '';
      if (obj[i] instanceof Array) {
        if (obj[i].length !== 0) {
          for (j = 0; j < obj[i].length; j++) {
            if (typeof obj[i][j] === 'object') {
              subs = globalNode.subscripts.slice(0);
              subs.push(i);
              subs.push(j + offset);
              setFast(obj[i][j], {global: globalName, subscripts: subs});
            } 
            else {
              value = obj[i][j];
              if (value === null) value = '';
              subs = globalNode.subscripts.slice(0);
              subs.push(i);
              subs.push(j + offset);
              ewd.mumps.set({global: globalName, subscripts: subs, data: value});
            }
          }
        }
      }
      if (typeof obj[i] !== 'object') {
        value = obj[i];
        if (value === null) value = '';
        subs = globalNode.subscripts.slice(0);
        subs.push(i);
        ewd.mumps.set({global: globalName, subscripts: subs, data: value});
      }   
      if (obj[i] instanceof Object && !(obj[i] instanceof Array)) {
        subs = globalNode.subscripts.slice(0);
        subs.push(i);
        setFast(obj[i], {global: globalName, subscripts: subs});
      }
    }
  };

  var setProperties = function(obj, globalNode) {
    var i;
    var j;
    var value;
    var prop;
    for (i in obj){
      if (obj[i] === null) obj[i] = '';
      if (obj[i] instanceof Array) {
        if (obj[i].length !== 0) {
          for (j = 0; j < obj[i].length; j++) {
            if (typeof obj[i][j] === 'object') {
              prop = i;
              setProperties(obj[i][j], globalNode.$(prop).$(j + offset));
            } 
            else {
              value = obj[i][j];
              if (value === null) value = '';
              value = fixNumericString(value);
              globalNode.$(i).$(j + offset)._value = value;
            }
          }
        }
      }
      if (typeof obj[i] !== 'object') {
        value = obj[i];
        if (value === null) value = '';
        prop = i;
        value = fixNumericString(value);
        globalNode.$(prop)._value = value;
      }   
      if (obj[i] instanceof Object && !(obj[i] instanceof Array)) {
        setProperties(obj[i], globalNode.$(i));
      }
    }
  };

  if (fast) {
    setFast(document, {global: globalName, subscripts: this._subscripts.slice(0)});
  }
  else {
    setProperties(document, this);
  }
};


module.exports = {
  init: init,
  Global: Global,
  GlobalNode: GlobalNode,
  deleteGlobal: deleteGlobal,
  deleteGlobalNode: deleteGlobalNode,
  function: fn,
  MumpsFn: MumpsFn,
  getGlobalDirectory: getGlobalDirectory,
  version: version,
  changeHandler: changeHandler
};
