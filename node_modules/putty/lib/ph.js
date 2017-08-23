'use strict';

var _ = require('lodash'),
    Q = require('q'),
    mixins = require('./mixins');

_.mixin(mixins);

exports.cb2promise = function () {
  return Q.nfcall.apply(Q, _.arrgs(arguments));
};

exports.makePromise = function (work) {
  if (!(_.isFunction(work)))
    throw new Error('Must specify work as function');
  return Q.Promise(work);
};

exports.allCompleted = Q.allSettled;

exports.isFulfilled = function (p) {
  return p.state === 'fulfilled';
};

exports.isRejected = function (p) {
  return p.state === 'rejected';
};
