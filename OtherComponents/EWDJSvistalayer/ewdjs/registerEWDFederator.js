var ewdGlobals = require('globalsjs');
var interface = require('cache');
var db = new interface.Cache();
var ok = db.open({
  path: '/srv/mgr',
  username: '_SYSTEM',
  password: 'innovate',
  namespace: 'VISTA'
});

ewdGlobals.init(db); 

var ewd = {
  mumps: ewdGlobals
};

var zewd = new ewd.mumps.GlobalNode('%zewd', []);
zewd._setDocument({
  "EWDLiteServiceAccessId": {
    "ewdfederator": {
      "secretKey": "$keepSecret!",
      "apps": {
        "raptor": true
      }
    }
  }
});

db.close();
