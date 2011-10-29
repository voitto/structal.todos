// Create the Task model.
var Task = Spine.Model.setup("Task", ["name", "done"]);

// Persist model between page reloads.
Task.extend(Spine.Model.Ajax);

Task.extend({
  // Return all active tasks.
  active: function(){
    return(this.select(function(item){ return !item.done; }));
  },

  // Return all done tasks.
  done: function(){
    return(this.select(function(item){ return !!item.done; }));    
  },

  // Clear all done tasks.
  destroyDone: function(){
    jQuery(this.done()).each(function(i, rec){ rec.destroy(); });
  }
});




jQuery(function($){
  
  tpl = '';
	$.ajax({
    type : 'GET',
		url : '/tpl/tasks/_show.html',
    success : function(req) {
      tpl = req;
    }
  });
  
  window.Tasks = Spine.Controller.create({
    tag: "li",
    
    proxied: ["render", "remove"],
    
    events: {
      "change   input[type=checkbox]": "toggle",
      "click    .destroy":             "destroy",
      "dblclick .view":                "edit",
      "keypress input[type=text]":     "blurOnEnter",
      "blur     input[type=text]":     "close"
    },
    
    elements: {
      "input[type=text]": "input",
      ".item": "wrapper"
    },
    
    init: function(){
      this.item.bind("update",  this.render);
      this.item.bind("destroy", this.remove);
    },
    
    render: function(){
      this.el.html(Mustache.to_html(tpl, this.item));
      this.refreshElements();
      return this;
    },
    
    toggle: function(){
      this.item.done = !this.item.done;
      this.item.save();      
    },
    
    destroy: function(){
      this.item.destroy();
    },
    
    edit: function(){
      this.wrapper.addClass("editing");
      this.input.focus();
    },
    
    blurOnEnter: function(e) {
      if (e.keyCode == 13) e.target.blur();
    },
    
    close: function(){
      this.wrapper.removeClass("editing");
      this.item.updateAttributes({name: this.input.val()});
    },
    
    remove: function(){
      this.el.remove();
    }
  });
  
  window.TaskApp = Spine.Controller.create({
    el: $("#tasks"),
    
    proxied: ["addOne", "addAll", "renderCount"],

    events: {
      "submit form":   "create",
      "click  .clear": "clear"
    },

    elements: {
      ".items":     "items",
      ".countVal":  "count",
      ".clear":     "clear",
      "form input": "input"
    },
    
    init: function(){
      Task.bind("create",  this.addOne);
      Task.bind("refresh", this.addAll);
      Task.bind("refresh change", this.renderCount);
      Task.fetch();
    },
    
    addOne: function(task) {
      var view = Tasks.init({item: task});
      this.items.append(view.render().el);
    },

    addAll: function() {
      Task.each(this.addOne);
    },
        
    create: function(){
      Task.create({name: this.input.val()});
      this.input.val("");
      return false;
    },
    
    clear: function(){
      Task.destroyDone();
    },
    
    renderCount: function(){
      var active = Task.active().length;
      this.count.text(active);
      
      var inactive = Task.done().length;
      this.clear[inactive ? "show" : "hide"]();
    }
  });
  
  window.App = TaskApp.init();


  

});