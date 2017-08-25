'use strict';

var _ = require('lodash'),
    util = require('util');

/**
 * Returns the value of the property at the specified
 * path within the specified target object
 *
 * @param {Object} target the target object
 * @param {String} path the path of the property to get. Example 'a.b.c'
 * @param {String} [delim] the delimiter used to separate the properties in the path.
 *                         Default is '.'
 * @return {Any} the value of the property at the path or undefined
 */
exports.at = function (target, path, delim) {
  if (!target) return undefined;
  if (_.isEmpty(path)) return target;

  var props = path.split((delim || '.'));

  return _.reduce(props, function (o, prop) {
    return !o ? undefined : o[prop];
  }, target);
};

exports.fmt = util.format;

/**
 * Returns an array by converting the specified arguments object
 *
 * @param {Object} args the arguments object to be converted to an array
 * @return {Array} the specified arguments object converted to an array
 */
exports.arrgs = function (args) {
  if (!_.isArguments(args)) return [];
  if (args.length === 1 && _.isArray(args[0])) return args[0];
  return _.toArray(args).slice();
};

/**
 * Returns a shallow clone of the specified source object
 * without the specified properties
 *
 * @param {Object} source the source object
 * @param {Array|String|String[]} excludes the names of
 *                                the properties to be excluded
 * @return {Object} a shallow clone of the source object without
 *                  the specified properties
 */
exports.except = function () {
  var args = this.arrgs(arguments),
      src = args[0],
      excludes = _.rest(args);

  if (_.isEmpty(excludes)) return src;
  if (excludes.length === 1 && _.isArray(excludes[0]))
    excludes = excludes[0];

  return _.pick(src, _.without.apply(_,
        [_.keys(src)].concat(excludes)));
};

/**
 * Merges objects passed to this method as arguments into one
 * object with attributes from all the objects
 *
 * @param {Object|Object...} objects the objects to merge together
 *
 * @return {Object} a single object after merging attributes from all specified objects
 */
exports.combine = function () {
  var args = this.arrgs(arguments);
  args.unshift({});
  return _.merge.apply(_, args);
};
