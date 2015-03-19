(function(window, $, d3, undefined) {
'use strict';

/**
 * Create a clone of object, works only with array and plain object type,
 * not working with type such as Date
 *
 * @param  {Object} obj
 * @return {Object} a clone of original obj
 */
window.clone = function (obj) {
    if(obj == null || typeof(obj) !== 'object') {
        return obj;
    }
    var temp = obj.constructor();
    for(var key in obj) {
        if(obj.hasOwnProperty(key)) {
            temp[key] = clone(obj[key]);
        }
    }
    return temp;
}

window.parseDate = d3.time.format('%m-%d-%Y %H%M%S').parse;

/**
 * Convert data into simple format like:
 * {date: Date, <key>: value}
 *
 * @param  {Array}         data  - original data
 * @param  {String|Index}  key   - the index to retrieve the desired value
 * @param  {Boolean}       isNum - if true, convert value to number, default true
 * @return {Array} a simpler clone of original data
 */
window.parseData = function (data, key) {
    var cloneData = clone(data);

    var result = [], i, d, o, n;

    for (i = 0; i < cloneData.length; i++) {
        d = cloneData[i];
        if (d[key] != null) {
            if (d.datetime == null) {
                throw new Error('requires datetime to parse date');
            }
            o = {date: parseDate(d.datetime)};
            n = Number(d[key]);
            if (n !== n) {
                throw new Error(JSON.stringify(d[key])+' is not a number');
            }
            o.val = n;
            result.push(o);
        }
    }

    return result;
}

window.RaptorChart = (function () {
    var _extend   = $.extend;

    var defaultOpt = {
        margin: {
            top: 20,
            right: 60,
            bottom: 70,
            left: 80
        },
        width: 800,
        height: 300,
        graph: {
            outterRadius: 6,
            innerRadius: 4
        },
        classPrefix: 'raptor-',
        maxColumns: 61,
        enableDateGap: true,
        xAxisFormatter: function (d) {
            if (d.toDateString) {
                return d.toDateString();
            }
        }
    };

    function parseDataSummary(data) {
        var result = [], prev, i, d, r;

        function propertyGetter(d) {
            return d.val;
        }

        prev = data[0];

        result.push({
            date:      prev.date,
            readings: [prev]
        });

        for (i = 1; i < data.length; i++) {
            d = data[i];
            if (prev.date.getDateStrAsNum() === d.date.getDateStrAsNum()) {
                result[result.length - 1].readings.push(d);
            } else {
                result.push({
                    date:      d.date,
                    readings: [d]
                });
            }
            prev = d;
        }

        for (i = 0; i < result.length; i++) {
            r      = result[i];
            r.max  = d3.max( r.readings, propertyGetter);
            r.min  = d3.min( r.readings, propertyGetter);
            r.mean = d3.mean(r.readings, propertyGetter);
        }

        return result;
    }

    function translateStr(x, y) {
        return 'translate(' + x + ',' + y + ')';
    }

    function isArray(obj) {
        return Object.prototype.toString.call(obj) === '[object Array]';
    }

    function flatArrary(arr) {
        var result = [];
        for (var i = 0; i < arr.length; i++) {
            if (isArray(arr[i])) {
                result = result.concat(flatArrary(arr[i]));
            } else {
                result.push(arr[i]);
            }
        }
        return result;
    }

    // (String: prefixes ...) -> (String: classNames ...) -> String
    function prefixClass() {
        var i;
        for (i = 0; i < arguments.length; i++) {
            if (isArray(arguments[i])) {
                return prefixClass.apply(undefined, (flatArrary(arguments)));
            }
        }
        var prefixes = [], aggregator = '';
        for (i = 0; i < arguments.length; i++) {
            aggregator += arguments[i];
            prefixes.push(aggregator);
        }
        return function () {
            var result = [];
            for (var j = 0; j < prefixes.length; j++) {
                for (var k = 0; k < arguments.length; k++) {
                    result.push(prefixes[j] + arguments[k]);
                }
            }
            return result.join(' ');
        };
    }

    Date.prototype.getDateStr = function () {
        function padStr(n, w, z) {
            z = z || '0';
            n = n + '';
            return n.length >= w ? n : new Array(w - n.length + 1).join(z) + n;
        }
        return '' + this.getFullYear() + padStr(this.getMonth() + 1, 2) + padStr(this.getDate(), 2);
    };

    Date.prototype.getDateStrAsNum = function () {
        return Number(this.getDateStr());
    };

    Date.prototype.getUnixDay = function () {
        return Math.floor((this.getTime() / 1000 / 60 - this.getTimezoneOffset()) / 60 / 24);
    };

    Date.parseDateStr = function (datetamp) {
        var parseDate = d3.time.format('%Y%m%d').parse;
        return parseDate(String(datetamp));
    };

    return function (sel, opts) {
        opts             = opts || {};
        this.opts        = opts = _extend(true, {}, defaultOpt, opts);
        this.sel         = sel;
        this.summaryData = {};
        this.dataOptions = {};
        this.dates       = [];

        var maxColumns = Math.floor(opts.width / (2 + opts.graph.outterRadius * 2));
        if (opts.maxColumns > maxColumns) {
            opts.maxColumns = maxColumns;
        }

        var _this  = this;
        var pf     = opts.classPrefix.split(' ');
        var mainPf = prefixClass(pf);
        var svg    = d3.select(sel).append('svg')
            .attr({
                'class':  mainPf('svg'),
                'width':  opts.width  + opts.margin.left + opts.margin.right,
                'height': opts.height + opts.margin.top  + opts.margin.bottom
            });
        var canvas = svg.append('g')
            .attr({
                'class':     mainPf('canvas'),
                'transform': translateStr(opts.margin.left, opts.margin.top)
            });

        var mouseoverG = canvas.append('g')
            .attr({
                'class': mainPf('mousemove')
            });

        var x  = d3.scale.ordinal().rangeBands([0, opts.width], 0, 0.5);
        x.fromDate = function (date) {
            var xDomain = x.domain(), d;
            for (var i = 0; i < xDomain.length; i++) {
                d = xDomain[i];
                if (d.toDateString && d.getDateStr() === date.getDateStr()) {
                    return this(d);
                }
            }
        };

        var y1 = d3.scale.linear().range([opts.height, 0]);
        var y2 = d3.scale.linear().range([opts.height, 0]);
        var y1Inverse = d3.scale.linear().domain([opts.height, 0]);
        var y2Inverse = d3.scale.linear().domain([opts.height, 0]);

        var xAxis = d3.svg.axis()
            .scale(x)
            .orient('bottom')
            .tickFormat(opts.xAxisFormatter);

        var xAxisG = canvas.append('g')
            .attr({
                'class':     mainPf('x', 'axis'),
                'transform': translateStr(0, opts.height)
            });

        var yAxis1 = this.yAxis1 = d3.svg.axis()
            .scale(y1)
            .orient('left');

        var yAxis2 = this.yAxis2 = d3.svg.axis()
            .scale(y2)
            .orient('right');

        function parseLine(summary, x, y) {
            var result = [],
                prev = summary[0],
                prevIndex = 0,
                type = 'solid',
                d,
                xCoord1,
                xCoord2;

            for (var i = 1; i < summary.length; i++) {
                d = summary[i];
                if (d.date.getUnixDay() - prev.date.getUnixDay() > 1) {
                    type = 'dotted';
                }
                xCoord1 = x.fromDate(prev.date);
                xCoord2 = x.fromDate(d.date);
                result.push({
                    start: {
                        x: xCoord1,
                        y: y(prev.readings[prev.readings.length - 1].val)
                    },
                    end: {
                        x: xCoord2,
                        y: y(d.readings[0].val)
                    },
                    type: type
                });
                type = 'solid';
                prev = d;
                prevIndex = i;
            }
            return result;
        }

        /**
         *
         * @param  {Object} d {mean: Number, max: Number, min: Number}
         * @param  {Number} i index
         * @return {String}   string of path
         */
        function getPathForDataPoint(d, y) {
            var w1 = opts.graph.outterRadius;
            return 'M'+(-w1)+' '+y(d.max)+
                'a'+w1+' '+w1+' 0 0 1 '+w1*2+' 0'+
                'L'+w1+' '+y(d.min)+
                'a'+w1+' '+w1+' 0 0 1 '+(-w1*2)+' 0Z';
        }

        function mergeDates (dates) {
            /**
             * @return {Array} [Date, 1, 2, 3, Date, Date, 6, 7, 8, Date]
             */
            function getXDomain(dates) {
                var result;
                if (!opts.enableDateGap) {
                    result = dates;
                } else {
                    var prev = dates[0],
                        j    = 1;
                    result = [prev];
                    for (var i = 1; i < dates.length; i++) {
                        if (dates[i].getUnixDay() - prev.getUnixDay() > 1) {
                            result.push(j++);
                            result.push(j++);
                            result.push(j++);
                        }
                        result.push(dates[i]);
                        prev = dates[i];
                        j += 1;
                    }
                }
                if (result.length > opts.maxColumns) {
                    result = result.slice(-opts.maxColumns);
                    while (typeof result[0] === 'number') {
                        result.shift();
                    }
                    return result;
                } else {
                    return result;
                }
            }

            var t1, t2;

            for (var i = 0; i < _this.dates.length; i++) {
                t1 = _this.dates[i].getDateStrAsNum();
                t2 = dates[0].getDateStrAsNum();
                if (t2 < t1) {
                    _this.dates.splice(i, 0, Date.parseDateStr(t2));
                    dates.shift();
                } else if (t2 === t1) {
                    dates.shift();
                }
            }

            _this.dates = _this.dates.concat(
                dates.map(function (d) {
                    return Date.parseDateStr(d.getDateStr());
                })
            );

            var xDomain = getXDomain(_this.dates);

            x.domain(xDomain);
            xAxisG.transition().duration(300).call(xAxis);
            xAxisG.selectAll('text')
                .style({'text-anchor': ''})
                .attr({'transform': 'translate(-5, 0) rotate(-45)'});

            mouseoverG.selectAll('rect').remove();
            mouseoverG.selectAll('rect')
                .data(xDomain)
                .enter()
                .append('rect')
                .attr({
                    'class': mainPf('data-point-background'),
                    'x': x,
                    'y': 0,
                    'height': opts.height,
                    'width': x.rangeBand()
                })
                .on('mouseenter', function () {
                    var $this = d3.select(this),
                        currentVal = $this.datum();
                    if (currentVal.toDateString) {
                        var key, summaries, i, d, j, val, valStr, opt;
                        for (key in _this.summaryData) {
                            summaries = _this.summaryData[key];
                            for (i = 0; i < summaries.length; i++) {
                                d = summaries[i];
                                if (d.date.getDateStr() === currentVal.getDateStr()) {
                                    // Render tooltips
                                    for (j = 0; j < d.readings.length; j++) {
                                        val    = d.readings[j].val;
                                        opt    = _this.dataOptions[key];
                                        valStr = opt.labelFormatter ? opt.labelFormatter(val) : String(val);
                                        makeTooltip(mouseoverG, x.fromDate(d.date) + x.rangeBand() / 2, opt.y(val), valStr, opt.position === 'right');
                                    }
                                    break;
                                }
                            }
                        }

                        // Highlight current column
                        $this.classed({
                            'highlight': true
                        });
                    }
                })
                .on('mouseleave', function () {
                    mouseoverG.selectAll('.tooltip').remove();
                    d3.select(this).classed({
                        'highlight': false
                    });
                });
        }

        this.drawData = function (data, options) {
            data.sort(function (a, b) {
                return a.date.getTime() - b.date.getTime();
            });

            var summary = parseDataSummary(data);
            this.summaryData[options.id] = summary;
            this.dataOptions[options.id] = options;

            mergeDates(summary.map(function (d) {
                return d.date;
            }));

            // Cut data points that's not in the range of x axis domain
            var xDomainStart = x.domain()[0].getUnixDay();
            if (summary[0].date.getUnixDay() !== xDomainStart) {
                var i = 0;
                for (var j = 0; j < summary.length; j++) {
                    if (summary[j].date.getUnixDay() >= xDomainStart) {
                        i = j;
                        break;
                    }
                }
                summary = summary.slice(i);
            }

            function parseYScale(position, summary, y, yInverse, yAxis, canvas) {

                function addDomainMargin(domain) {
                    var range = domain[1] - domain[0];
                    if (range === 0) {
                        range = domain[0] * 0.1;
                    }
                    domain[0] -= range * 0.1;
                    domain[1] += range * 0.1;
                    return domain;
                }

                var yDomain = d3.extent(
                        summary.map(function (d) { return d.max; })
                        .concat(summary.map(function (d) { return d.min; }))
                    ).sort(function (a, b) { return a - b; });

                addDomainMargin(yDomain);

                y.domain(yDomain);
                yInverse.range(yDomain);

                if (options.labelFormatter) {
                    yAxis.tickFormat(options.labelFormatter);
                }

                if (options.yAxisTickCount != null) {
                    yAxis.ticks(options.yAxisTickCount);
                }

                canvas.append('g')
                    .attr({
                        'class': mainPf('y', 'axis', 'axis-' + position),
                        'transform': position === 'right' ? translateStr(opts.width, 0) : null
                    })
                    .call(yAxis);
            }

            var y, groupPf = prefixClass(pf, options.classPrefix);

            if (options.position === 'left') {
                parseYScale(options.position, summary, y1, y1Inverse, yAxis1, canvas);
                y = y1;
            } else {
                parseYScale(options.position, summary, y2, y2Inverse, yAxis2, canvas);
                y = y2;
            }

            // Save reference to y scale, to user later in tooltips
            options.y = y;

            var dataPoints = canvas.insert('g', '.' + mainPf('mousemove'))
                .attr({
                    'class':     groupPf('data-group'),
                    'transform': translateStr(x.rangeBand() / 2, 0)
                })
                .selectAll('.' + pf + 'data-point')
                .data(summary)
                .enter()
                .append('g')
                .attr({
                    'class':     groupPf('data-point'),
                    'transform': function (d) {
                        return translateStr(x.fromDate(d.date), 0);
                    }
                });

            dataPoints
                .filter(function (d) {
                    return d.readings.length > 1;
                })
                .append('path')
                .attr({
                    'class': groupPf('data-point-bar'),
                    'd': function (d) {
                        return getPathForDataPoint(d, y);
                    }
                });

            dataPoints
                .selectAll('circle')
                .data(function (d) {
                    return d.readings;
                })
                .enter()
                .append('circle')
                .attr({
                    'class': groupPf('data-point-dot'),
                    'r': opts.graph.innerRadius,
                    'cx': 0,
                    'cy': function (d) {
                        return y(d.val);
                    }
                });

            // Render lines
            //
            var lines = parseLine(summary, x, y);

            canvas
                .insert('g', '.' + mainPf('data-group'))
                .attr({
                    'class':     groupPf('data-lines'),
                    'transform': translateStr(x.rangeBand() / 2, 0)
                })
                .selectAll('.' + pf + 'data-line')
                .data(lines)
                .enter()
                .append('line')
                .attr({
                    'class': groupPf('data-line'),
                    'x1': function(d) { return d.start.x; },
                    'y1': function(d) { return d.start.y; },
                    'x2': function(d) { return d.end.x;   },
                    'y2': function(d) { return d.end.y;   }
                })
                .classed({
                    'dotted': function (d) {
                        return d.type === 'dotted';
                    }
                });

            // Add Axis Title
            //
            var axisTitleTransform = options.position === 'left' ?
                translateStr(-opts.margin.left + 16, 0) + ' rotate(-90)' :
                translateStr(opts.margin.right - 16, 0) + ' rotate(90)';

            var axisTitleGroup = canvas.append('g')
                .attr({
                    'class': mainPf('title-group-' + options.position) + ' ' + groupPf('title-group'),
                    'transform': axisTitleTransform
                });

            var textAnchorX = options.position === 'left' ? - opts.height / 2 : opts.height / 2,
                textAnchorY = options.position === 'left' ? 0 : - opts.width;

            var axisTitleTextAnchor;
            if (options.displayLegend) {
                axisTitleTextAnchor = options.position === 'left' ? 'begin' : 'end';
            } else {
                axisTitleTextAnchor = 'middle';
            }

            axisTitleGroup.append('text')
                .attr({
                    'x': textAnchorX,
                    'y': textAnchorY
                })
                .text(options.title);

            // Set text-anchor only when it's not `begin`,
            // otherwise IE 9 will throw error
            if (axisTitleTextAnchor !== 'begin') {
                axisTitleGroup.style({'text-anchor': axisTitleTextAnchor});
            }

            if (options.displayLegend) {
                var legendLineY = textAnchorY - 4;

                axisTitleGroup.append('line')
                    .attr({
                        'class': groupPf('data-line'),
                        'x1': options.position === 'left' ? textAnchorX - 10 : textAnchorX + 10,
                        'y1': legendLineY,
                        'x2': options.position === 'left' ? textAnchorX - 60 : textAnchorX + 60,
                        'y2': legendLineY
                    });
            }
        };

        var TOOPTIP_WIDTH       = 40,
            TOOPTIP_HEIGHT      = 15,
            TOOPTIP_OFFSET      = 3,
            TOOPTIP_ARROW_WIDTH = 5;

        function calcTooltipTransform(x, y) {
            return 'translate('+(x - TOOPTIP_WIDTH - TOOPTIP_OFFSET)+','+
                (y - TOOPTIP_HEIGHT - TOOPTIP_OFFSET)+')';
        }

        function makeTooltip(parent, x, y, val, isFlip) {
            var tooltip = parent.append('g').classed({tooltip: true});

            tooltip.append('path')
                .attr('d', function() {
                    var leftTopX      = 0,
                        leftTopY      = 0,
                        rightTopX     = TOOPTIP_WIDTH,
                        rightTopY     = 0,
                        rightBottom1X = TOOPTIP_WIDTH,
                        rightBottom1Y = TOOPTIP_HEIGHT - TOOPTIP_ARROW_WIDTH,
                        rightBottomX  = TOOPTIP_WIDTH + TOOPTIP_OFFSET,
                        rightBottomY  = TOOPTIP_HEIGHT + TOOPTIP_OFFSET,
                        rightBottom2X = TOOPTIP_WIDTH - TOOPTIP_ARROW_WIDTH,
                        rightBottom2Y = TOOPTIP_HEIGHT,
                        leftBottomX   = 0,
                        leftBottomY   = TOOPTIP_HEIGHT;
                    return 'M'+leftTopX+','+leftTopY+
                        'L'+rightTopX+','+rightTopY+
                        'L'+rightBottom1X+','+rightBottom1Y+
                        'L'+rightBottomX+','+rightBottomY+
                        'L'+rightBottom2X+','+rightBottom2Y+
                        'L'+leftBottomX+','+leftBottomY;
                })
                .attr('transform', isFlip ?
                    'rotate(180) translate('+(-(TOOPTIP_WIDTH+TOOPTIP_OFFSET)*2)+','+(-(TOOPTIP_HEIGHT+TOOPTIP_OFFSET)*2)+')' :
                    '');

            tooltip.append('text')
                .attr({
                    'text-anchor': 'middle',
                    x: TOOPTIP_WIDTH / 2,
                    y: TOOPTIP_HEIGHT / 2 + 3
                })
                .attr('transform', isFlip ?
                    'translate('+(TOOPTIP_WIDTH+TOOPTIP_OFFSET+3)+','+(TOOPTIP_HEIGHT+TOOPTIP_OFFSET+3)+')' :
                    '')
                .text(val);

            tooltip.setTransform = function(x, y) {
                this.attr('transform', calcTooltipTransform(x, y));
            };

            tooltip.setTransform(x, y);

            tooltip.setVal = function(val) {
                this.selectAll('text').text(val);
            };

            tooltip.setVal(val);

            return tooltip;
        }

        function roundTemperature(t) {
            return Math.round(t * 10) / 10;
        }
    };
}());

}(window, jQuery, d3));

var makeTemperatureGraph = function(temperatures, date, temperature, location) { //the function for the temperature graph

    var temperatureData = parseData(temperatures, 'temperature');

    var chart = new RaptorChart(location, {
        margin: {
            top: 10,
            right: 10,
            bottom: 80,
            left: 80
        },
        width: 400,
        height: 150,
        maxColumns: 5,
        enableDateGap: false,
        xAxisFormatter: function (d) {
            function pad(n, width, z) {
                z = z || '0';
                n = n + '';
                return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
            }

            if (d.getDate) {
                return (d.getMonth() + 1) + '/' + pad(d.getDate(), 2) + '/' + d.getFullYear();
            }
        }
    });

    chart.drawData(temperatureData, {
        id: 'temperature',
        position: 'left',
        title: 'Temperature',
        classPrefix: 'temperature-',
        labelFormatter: function (v) {
            var m = /(\d+(\.\d)?)\d*/.exec(v);
            return (m[2] ? m[1] : m[1] + '.0') + '°F';
        },
        yAxisTickCount: 5
    });
};

var makeeGFRGraph = function(egfrs, date, egfr, location) { //the function for making an egfr graph

    if (egfrs.length === 0) {
        return;
    }

    var egfrData = parseData(egfrs, 'egfr');

    var chart = new RaptorChart(location, {
        xAxisFormatter: function (d) {
            function pad(n, width, z) {
                z = z || '0';
                n = n + '';
                return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
            }

            if (d.getDate) {
                return (d.getMonth() + 1) + '/' + pad(d.getDate(), 2) + '/' + d.getFullYear();
            }
        }
    });

    chart.drawData(egfrData, {
        id: 'egfr',
        position: 'left',
        title: 'eGFR',
        classPrefix: 'egfr-',
        labelFormatter: function (v) {
            return v;
        }
    });
};

var makeVitalsGraph = function(vitals, date, temperature, pulse, location) {

    if (vitals.length === 0) {
        return;
    }

    function parseTemperature(data) {
        return parseData(data, 'temperature');
    }

    function parsePulse(data) {
        return parseData(data, 'pulse');
    }

    var temperatureData = parseTemperature(vitals);
    var pulseData = parsePulse(vitals);

    var chart = new RaptorChart(location, {
        xAxisFormatter: function (d) {
            function pad(n, width, z) {
                z = z || '0';
                n = n + '';
                return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
            }

            if (d.getDate) {
                return (d.getMonth() + 1) + '/' + pad(d.getDate(), 2) + '/' + d.getFullYear();
            }
        }
    });

    chart.drawData(pulseData, {
        id: 'pulse',
        position: 'right',
        title: 'Pulse',
        classPrefix: 'pulse-',
        labelFormatter: function (v) {
            return v;
        },
        displayLegend: true
    });

    chart.drawData(temperatureData, {
        id: 'temperature',
        position: 'left',
        title: 'Temperature',
        classPrefix: 'temperature-',
        labelFormatter: function (v) {
            var m = /(\d+(\.\d)?)\d*/.exec(v);
            return (m[2] ? m[1] : m[1] + '.0') + '°F';
        },
        displayLegend: true
    });
}; //end function makeVitalsGraph

jQuery(document).ready(function($) {
    var thumbnailChart = $('#thumbnail-chart'),
        labsChart = $('#labs-chart'),
        vitalsChart = $('#vitals-chart');

    //these should be the name of and keys from the original data array and the ID of where you want the graph to show up
    // console.log('chartThumbnail', JSON.stringify(chartThumbnail));
    if (thumbnailChart.length && chartThumbnail.length) {
        try {
            makeTemperatureGraph(chartThumbnail, "date", "temperature", "#thumbnail-chart");
        } catch (e) {
            console.error(e);
        }
    }
    
    if (labsChart.length && chartLabs.length) {
        try {
            makeeGFRGraph(chartLabs, "date", "egfr", "#labs-chart");
        } catch (e) {
            console.error(e);
        }
    }
    
    // console.log('chartVitals', JSON.stringify(chartVitals));
    if (vitalsChart.length && chartVitals.length) {
        try {
            makeVitalsGraph(chartVitals, "date", "temperature", "pulse", "#vitals-chart");
        } catch (e) {
            console.error(e.stack);
        }
    }
});
