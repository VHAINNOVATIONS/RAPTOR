/**
 * @file forena_query.js
 * Javascript library for forena query
 */
var forenaSQLEditor = new function() { 
  // Initialize the object, binding the autocomplete function to the appropriate contorl
  // THis should be called in the attach behavior
  this.control = {}; // Jquery Control selector
  this.instance = this; 
  this.testValues = ["one", "two", "three"]; 
  this.position = 0; 
  this.autocompletePath = ''; 
  this.tokens = []; 
  this.tables = []; 
  this.reserved = ['select', 
                   'from', 
                   'inner', 
                   'left', 
                   'join', 
                   'right', 
                   'cross', 
                   'where', 
                   'having', 
                   'order', 
                   'group', 
                   'by',
                   'on',
                   'in']; 
  this.states = [
                   'select', 
                   'from', 
                   'join', 
                   'on', 
                   'where', 
                   'having', 
                   'order', 
                   'group'
                   ]; 
  this.repos = ''; 
  var self = this; 
  
  /**
   * Parse the list of tables/aliases from the sql. 
   */ 
  this.tableAliases = function() { 
    // Split on commas
    clause = self.parseTables(); 
    var tables = {}; 
    var parts = clause.split(","); 
    for (var i=0; i< parts.length; i++) { 
      if (parts[i].trim() && parts[i].search('--') == -1) { 
        var p = parts[i].trim().split(/\s+/);
        if (p.length > 1) {
          tables[p[1]] = p[0];  
        }
        else { 
          tables[p[0]] = p[0]
        }
      }
    }
    return tables; 
  }
  
  /**
   * Parse table aliases from the sql statement
   */
  this.parseTables = function () { 
    sql = self.control.get(0).value; 
    // Split array on words. 
    var words = sql.split(/\s+/); // Break apart the sql statement by white space. 
    var word=''; 
    var state = '';
    var table_clause = ''; // rough comma separated list of tables/aliases for further processing
    var wcnt = 0; // Word count within the statement section
    for( var i=0; i<words.length; i++) {
      // Remove the parents as we don't need them for this level of parsing. 
      word = words[i].toLowerCase().replace(/\(|\)/g,"");
        
      // Certain words mean we are in a different section of the sql. 
      if (self.states.indexOf(word) != -1) {
        state = word; 
        wcnt = 0; 
      }
      
      // non reserved words are candidates for table aliases. 
      if (self.reserved.indexOf(word) == -1) {
        wcnt++; 
        switch (state) { 
         case 'from': 
         case 'join': 
           if (wcnt == 1) {
             table_clause = table_clause + ','; 
           }
           table_clause = table_clause + ' ' + word; 
           break; 
        }
      }
    }
    return table_clause; 
  }
  
  /**
   * Initialization function for setting up the editor. 
   * Should fire on attach (document ready). 
   */
  this.init = function (context, settings) {
    self.control = jQuery('#edit-config-file', context); 
    self.control.keydown(self.keyDownHandler); 
    self.control.keyup(self.keyUpHandler); 
    self.autocompletePath = settings.basePath + "forena-query/table_autocomplete_simple/sampledb"; 
    self.repos = settings.forenaSQLEditor.repos; 
    self.tokens = []; 
    self.term = ''; 
    self.control.autocomplete(
      { 
        source : self.autocompleteSource, 
        minLength : 1, 
        focus : function () { return false; }, 
        select : self.selectHandler, 
      }
    ); 
  };
  
  /**
   * Determine the caret position registered on keyup. 
   */
  this.getCaretPosition = function() {
    var el = self.control.get(0);
    var pos = 0;
    if('selectionStart' in el) {
        pos = el.selectionStart;
    } else if('selection' in document) {
        el.focus();
        var Sel = document.selection.createRange();
        var SelLength = document.selection.createRange().text.length;
        Sel.moveStart('character', -el.value.length);
        pos = Sel.text.length - SelLength;
    }
    self.position = pos; 
  };
  
  /**
   * Move the caret position to a specified place in the cursor. 
   */
  this.setCaretPosition = function(pos) { 
    c = self.control.get(0); 
    if (c.setSelectionRange) {
      c.setSelectionRange(pos, pos);
    } else if (c.createTextRange) {
      var range = c.createTextRange();
      range.collapse(true);
      range.moveEnd('character', pos);
      range.moveStart('character', pos);
      range.select();
    }
  }; 
  
  //Determine the current term 
  this.currentTerm = function(request) { 
    // Get the string up to the position. 
    var s =  self.control.val().slice(0, self.position); 
    var term = /\S+$/.exec(s);
    if (term) { 
      self.term = term.toString(); 
    }
    else { 
      self.term = ''; 
    }
    return self.term; 
  };
  
  /**
   * New function for determining dropdown contents based on last term. 
   */
  this.autocompleteSource = function(request, response) { 
    self.reponse = response; 
    var t = self.currentTerm(); 
    var table = ''; 
    var alias = ''; 
    table = '';
    alias = ''; 
    if (t.search('.') != -1) { 
      aliases = self.tableAliases(); 
      var a = t.substring(0, t.indexOf('.')); 
      if (aliases[a]) { 
        table = aliases[a];
        alias = a; 
        t = t.substring(t.indexOf('.')+1); 
      }
      
    }
    jQuery.getJSON(
      self.autocompletePath, 
      {
        term : t,
        repos : self.repos, 
        table : table,
        alias : alias,
      }, 
      self.jsonSuccess
    ); 
  };
  
  /**
   * Ajax return handler 
   * saves away raw response and then chains autocomplete response. 
   */
  this.jsonSuccess = function(data) { 
    console.log(data); 
    self.tokens = data; 
    self.reponse(data); 
  };
  
  /**
   * Inserts the term into the specified location of the textarea. 
   */
  this.select = function(token) { 
    var s = self.control.val().slice(0, self.position).lastIndexOf(self.term); 
    var end = self.control.val().slice(self.position); 
    var start = self.control.val().slice(0, s);
    self.control.get(0).value = start + token + end;
    // Make sure to close the widget. 
    self.control.autocomplete("close");
    self.setCaretPosition(s + token.length);
    
    
  };
  
  this.selectHandler = function (event, ui) { 
    self.select(ui.item.value); 
    if (event.keyCode == jQuery.ui.keyCode.TAB) { 
      event.preventDefault(); 
      self.control.get(0).focus(); 
    }
    return false; 
  };
  
  this.keyUpHandler = function (event) { 
    self.getCaretPosition(); 
  };
  
  /*
   * Custom keyboard handler
   */
  this.keyDownHandler = function (event) { 
    // Prevent the default autocomplete behavior of changing focus after hitting the tab key. 
    if (event.keyCode === jQuery.ui.keyCode.TAB) { 
      event.preventDefault(); 
      // Select the first item in the array
      if (self.tokens && self.tokens.length > 0) { 
        var token = self.tokens[0]; 
        if (token) self.select(token); 
      }

    }
  };
  
};

Drupal.behaviors.forena_query_sql_editor = { 
    attach : forenaSQLEditor.init
};