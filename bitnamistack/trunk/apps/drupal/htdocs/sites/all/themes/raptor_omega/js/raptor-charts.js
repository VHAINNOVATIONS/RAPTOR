var makeTemperatureGraph = function(temperatures, date, temperature, location) { //the function for the temperature graph

    var margin = {
        top: 50,
        right: 50,
        bottom: 100,
        left: 100
    }, //dimensions
        width = 500 - margin.left - margin.right,
        height = 250 - margin.top - margin.bottom;

    var parseDate = d3.time.format("%m-%d-%Y").parse; //turning the date in the data into a Date object

    var x = d3.time.scale() //x scale
    .range([0, width]);

    var y = d3.scale.linear() //y scale
    .range([height, 0]);

    var xAxis = d3.svg.axis() //define x axis
    .scale(x)
        .tickFormat(d3.time.format("%m/%d"))
        .orient("bottom")
        .ticks(3);

    var yAxis = d3.svg.axis() //define y axis
    .scale(y)
        .orient("left")
        .ticks(5);

    var line = d3.svg.line() //define the line
    .x(function(d) {
        return x(d.date);
    })
        .y(function(d) {
            return y(d.temperature);
        });

    var svg = d3.select(location).append("svg") //add the chart to the body of the page
    .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
        .attr("class", "temperature-graph");

    temperatures.forEach(function(d) { //pull the data out of the array
        d.date = parseDate(d.date);
        d.temperature = +d.temperature;
    });

    x.domain(d3.extent(temperatures, function(d) {
        return d.date;
    }));
    y.domain([d3.min(temperatures, function(d) {
        return d.temperature - .5;
    }), d3.max(temperatures, function(d) {
        return d.temperature + .5;
    })]); //adding some vertical space below the lowest value and above the highest value

    svg.append("g") //add the x axis
    .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis)
        .append('text')
        .text("Date")
        .attr('dx', '9em')
        .style('font-weight', 'bold')
        .attr('dy', '3em');

    svg.append("g") //add the y axis
    .attr("class", "y axis")
        .call(yAxis)
        .append("text")
        .attr("transform", "rotate(-90)")
        .attr("y", 6)
        .attr("dx", "0em")
        .attr("dy", "-5em")
        .style("text-anchor", "end")
        .style('font-weight', 'bold')
        .text("Temperature");

    svg.selectAll("line.x") //intermediate vertical lines
    .data(x.ticks(d3.time.days, 1))
        .enter().append("line")
        .attr("class", "x")
        .attr("x1", x)
        .attr("x2", x)
        .attr("y1", 0)
        .attr("y2", height)
        .style("stroke", "#ccc");

    // Draw Y-axis grid lines
    svg.selectAll("line.y") //intermediate horizontal lines
    .data(y.ticks())
        .enter().append("line")
        .attr("class", "y")
        .attr("x1", 0)
        .attr("x2", width)
        .attr("y1", y)
        .attr("y2", y)
        .style("stroke", "#ccc");

    svg.append("path") //adding actual graph line
    .datum(temperatures)
        .attr("class", "line")
        .attr("d", line);

    svg.selectAll("dot") //adding data points
    .data(temperatures)
        .enter().append("circle")
        .attr("r", 5)
        .attr("cx", function(d) {
            return x(d.date);
        })
        .attr("cy", function(d) {
            return y(d.temperature);
        })
        .attr("stroke", "blue")
        .attr("class", function(d) {
            return d.flag;
        })
        .attr("fill", "blue")
        .text(function(d) {
            if (d.flag === "MISSING") {
                return "NO VALUE COLLECTED";
            } else {
                return (d.date.getMonth() + 1 + "/" + d.date.getDate() + "/" + (parseInt(d.date.getYear()) + 1900)) + " - " + d.temperature;
            }
        });;
};

var makeeGFRGraph = function(egfrs, date, egfr, location) { //the function for making an egfr graph

    var margin = {
        top: 50,
        right: 50,
        bottom: 100,
        left: 100
    }, //spacing and margins
        width = 1000 - margin.left - margin.right,
        height = 550 - margin.top - margin.bottom;

    var parseDate = d3.time.format("%m-%d-%Y").parse; //turning the date in the data into a Date object

    var x = d3.time.scale() //x scale
    .range([0, width]);

    var y = d3.scale.linear() //y scale
    .range([height, 0]);


    var xAxis = d3.svg.axis() //define x axis
    .scale(x)
        .ticks(d3.time.days, 1)
        .tickFormat(d3.time.format("%m/%d/%Y"))
        .orient("bottom");

    var yAxis = d3.svg.axis() //define y axis
    .scale(y)
        .orient("left");

    var line = d3.svg.line() //define the data line
    .x(function(d) {
        return x(d.date);
    })
        .y(function(d) {
            return y(d.egfr);
        });

    var svg = d3.select(location).append("svg") //add the map to the body of the page
    .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
        .attr("class", "egfr-graph");

    egfrs.forEach(function(d) { //get the data out of the array
        d.date = parseDate(d.date);
        d.position = egfrs.indexOf(d);
        d.temperature = +d.temperature;
    });

    x.domain(d3.extent(egfrs, function(d) {
        return d.date;
    }));
    y.domain([d3.min(egfrs, function(d) {
        return d.egfr - .5;
    }), d3.max(egfrs, function(d) {
        return d.egfr + .5;
    })]); //adding some vertical space below the lowest value and above the highest value


    svg.append("g") //add the x axis to the chart
    .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis)
        .append('text')
        .text("Date")
        .attr('dx', '25em')
        .style('font-weight', 'bold')
        .attr('dy', '3em');

    svg.append("g") //add the y axis to the chart
    .attr("class", "y axis")
        .call(yAxis)
        .append("text")
        .attr("transform", "rotate(-90)")
        .attr("y", 6)
        .attr("dx", "-14em")
        .attr("dy", "-5em")
        .style('font-weight', 'bold')
        .text("eGFR");

    svg.selectAll("line.x") //intermediate vertical lines
    .data(x.ticks(d3.time.days, 1))
        .enter().append("line")
        .attr("class", "x")
        .attr("x1", x)
        .attr("x2", x)
        .attr("y1", 0)
        .attr("y2", height)
        .style("stroke", "#ccc");

    // Draw Y-axis grid lines
    svg.selectAll("line.y") //intermediate horizontal lines
    .data(y.ticks())
        .enter().append("line")
        .attr("class", "y")
        .attr("x1", 0)
        .attr("x2", width)
        .attr("y1", y)
        .attr("y2", y)
        .style("stroke", "#ccc");

    svg.append("path") //add the actual line to the chart
    .datum(egfrs)
        .attr("class", "line")
        .attr("d", line);

    svg.selectAll("dot") //add the data points to the chart
    .data(egfrs)
        .enter().append("circle")
        .attr("r", 5)
        .attr("cx", function(d) {
            return x(d.date);
        })
        .attr("cy", function(d) {
            return y(d.egfr);
        })
        .attr("stroke", "blue")
        .attr("fill", "blue")
        .attr("class", function(d) {
            return d.flag;
        })
        .append("svg:title")
        .text(function(d) {
            if (d.flag === "MISSING") {
                return "NO VALUE COLLECTED";
            } else {
                return (d.date.getMonth() + 1 + "/" + d.date.getDate() + "/" + (parseInt(d.date.getYear()) + 1900)) + " - " + d.egfr;
            }
        });;
};

var makeVitalsGraph = function(vitals, date, temperature, pulse, location) {

    var margin = {
        top: 50,
        right: 100,
        bottom: 100,
        left: 100
    },
        width = 1000 - margin.left - margin.right,
        height = 550 - margin.top - margin.bottom;

    var parseDate = d3.time.format("%m-%d-%Y").parse;

    var x = d3.time.scale() //x scale
    .range([0, width]);

    var y1 = d3.scale.linear() //y1 scale
    .range([height, 0]);

    var y2 = d3.scale.linear() //y2 scale
    .range([height, 0]);

    var xAxis = d3.svg.axis() //date axis
    .scale(x)
        .ticks(d3.time.days, 1) //only one label per day
    .tickFormat(d3.time.format("%m/%d/%Y")) //making the labels consistently formatted
    .orient("bottom");

    var y1Axis = d3.svg.axis() //left hand temperature axis
    .scale(y1)
        .orient("left");

    var y2Axis = d3.svg.axis() //right hand pulse axis
    .scale(y2)
        .orient("right");

    var line = d3.svg.line() //temperature line
    .x(function(d) {
        return x(d.date);
    })
        .y(function(d) {
            return y1(d.temperature);
        });

    var line2 = d3.svg.line() //pulse line
    .x(function(d) {
        return x(d.date);
    })
        .y(function(d) {
            return y2(d.pulse);
        });

    var svg = d3.select(location).append("svg") //adding the map to the page
    .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
        .attr("class", "vitals-graph");

    vitals.forEach(function(d) { //pulling the variables out of the data
        d.date = parseDate(d.date);
        d.temperature = +d.temperature;
        d.pulse = +d.pulse;
    });

    x.domain(d3.extent(vitals, function(d) {
        return d.date;
    }));
    y1.domain([d3.min(vitals, function(d) {
        return d.temperature - .5;
    }), d3.max(vitals, function(d) {
        return d.temperature + .5;
    })]);
    y2.domain([d3.min(vitals, function(d) {
        return d.pulse - .5;
    }), d3.max(vitals, function(d) {
        return d.pulse + .5;
    })]); //adding some vertical space below the lowest value and above the highest value

    svg.append("g") //add the x axis
    .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis)
        .append('text')
        .text("Date")
        .attr('dx', '23em')
        .style('font-weight', 'bold')
        .attr('dy', '3em');

    svg.append("g") //add the left y axis
    .attr("class", "y axis")
        .call(y1Axis)
        .append("text")
        .attr("y", 6)
        .attr("dx", "-15em")
        .attr("dy", "-5em")
        .attr("fill", 'blue')
        .style('font-weight', 'bold')
        .text("Temperature")
        .attr("transform", "rotate(-90)");

    svg.append("g") //add the right y axis
    .attr("class", "y axis axisRight")
        .attr("transform", "translate(" + width + " ,0)")
        .attr("y", 6)
        .call(y2Axis)
        .append("text")
        .text("Pulse")
        .style('font-weight', 'bold')
        .attr("dx", "-14em")
        .attr('dy', '5em')
        .attr("fill", 'red')
        .attr("transform", "rotate(-90)");

    svg.selectAll("line.x") //add intermediate axis lines (vertical)
    .data(x.ticks(d3.time.days, 1))
        .enter().append("line")
        .attr("class", "x")
        .attr("x1", x)
        .attr("x2", x)
        .attr("y1", 0)
        .attr("y2", height);

    svg.selectAll("line.y1") //add intermediate axist lines (horizontal) for the first y axis only so we don't have lines going every which way
    .data(y1.ticks())
        .enter().append("line")
        .attr("class", "y1")
        .attr("x1", 0)
        .attr("x2", width)
        .attr("y1", y1)
        .attr("y2", y1);

    svg.append("path") //add the first line to the chart
    .datum(vitals)
        .attr("class", "line")
        .attr("d", line);

    svg.append("path") //add the second line to the chart
    .datum(vitals)
        .attr("class", "line line2")
        .attr("d", line2);

    svg.selectAll("dot") //add temperature dots
    .data(vitals)
        .enter().append("circle")
        .attr("r", 5)
        .attr("cx", function(d) {
            return x(d.date);
        })
        .attr("cy", function(d) {
            return y1(d.temperature);
        })
        .attr("stroke", "red")
        .attr("fill", "red")
        .attr("class", function(d) {
            return d.tempFlag;
        }) //add the class "generated" if they're generated
    .append("svg:title")
        .text(function(d) {
            if (d.tempFlag === "MISSING") {
                return "NO VALUE COLLECTED";
            } else {
                return (d.date.getMonth() + 1 + "/" + d.date.getDate() + "/" + (parseInt(d.date.getYear()) + 1900)) + " - " + d.temperature + " F";
            }
        });

    svg.selectAll("dot") //add pulse dots
    .data(vitals)
        .enter().append("circle")
        .attr("r", 5)
        .attr("cx", function(d) {
            return x(d.date);
        })
        .attr("cy", function(d) {
            return y2(d.pulse);
        })
        .attr("stroke", "blue")
        .attr("fill", "blue")
        .attr("class", function(d) {
            return d.pulseFlag;
        })
        .append("svg:title")
        .text(function(d) {
            if (d.pulseFlag === "MISSING") {
                return "NO VALUE COLLECTED";
            } else {
                return (d.date.getMonth() + 1 + "/" + d.date.getDate() + "/" + (parseInt(d.date.getYear()) + 1900)) + " - " + d.pulse;
            }
        });; //add the class "generated" if they're generated
}; //end function makeVitalsGraph

jQuery(document).ready(function($) {
    //these should be the name of and keys from the original data array and the ID of where you want the graph to show up
    makeTemperatureGraph(chartThumbnail, "date", "temperature", "#thumbnail-chart"); 
    makeeGFRGraph(chartLabs, "date", "egfr", "#labs-chart");
    makeVitalsGraph(chartVitals, "date", "temperature", "pulse", "#vitals-chart");
});