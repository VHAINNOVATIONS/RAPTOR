var federator = require('ewd-federator');

var params = {
  restPort: 8080,
  poolSize: 2,
  traceLevel: 3,
      database: {
        type: 'cache',
        path:"c:\\InterSystems\\Cache\\Mgr",
        username: "_SYSTEM",
        password: "SYS",
        namespace: "EDU"
      },
  server: {

    RaptorEwdVista: {
      host: '127.0.0.1',  // if federator installed on same physical machine as EWD.js / VistA
      port: 8082,
      ssl: false,
      ewdjs: true,
      accessId: 'ewdfederator',  // change as needed
      secretKey: 'apass'  // change as needed
    }
  },

  service: {
    raptor: {
      module: 'raptor',
      service: 'parse',
      contentType: 'application/json'
    }
  }

};

federator.start(params);