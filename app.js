
var Task = Spine.Model.sub();

Task.configure("Task", "name", "done");

Task.extend(Spine.Model.Ajax);


Task.extend({
  active: function(){
    return this.select(function(item) {
       return !item.done;
     });
  },
  done: function(){
    return this.select(function(item) {
       return !!item.done;
     });
  },
  destroyDone: function(){
     var rec, _i, _len, _ref, _results;
      _ref = this.done();
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        rec = _ref[_i];
        _results.push(rec.destroy());
      }
      return _results;
  }
});

jQuery(function($){
  
  var Tasks = Spine.Controller.sub({
    
    events: {
      "change   input[type=checkbox]": "toggle",
      "click    .destroy": "remove",
      "dblclick .view": "edit",
      "keypress input[type=text]": "blurOnEnter",
      "blur     input[type=text]": "close"
    },
    
    elements: {
       "input[type=text]": "input"
    },

    init: function(){
      this.item.bind("update", this.proxy(this.render));
    },

    render: function(){
      var el = this;
      $.get('tpl/tasks/_show.html', function(data) {
        el.html(Mustache.to_html(data, el.item));
      });
      return this;
    },
    
    toggle:function(){
      this.item.done = !this.item.done;
      return this.item.save();
    },
    
    remove: function(){
       return this.item.destroy();
    },

    edit: function(){
      this.el.addClass("editing");
      return this.input.focus();
    },

    blurOnEnter: function(){
       if (e.keyCode === 13) {
          return e.target.blur();
        }
    },
    
    close: function(){
        this.el.removeClass("editing");
        return this.item.updateAttributes({
          name: this.input.val()
        });
    }

  });

  var TaskApp = Spine.Controller.sub({
    
    events: {
      "submit form": "create",
      "click  .clear": "clear"
    },
    
    elements: {
      ".items": "items",
      ".countVal": "count",
      ".clear": "clear",
      "form input": "input"
    },

    init: function(){
      Task.bind("create", this.proxy(this.addOne));
      Task.bind("refresh", this.proxy(this.addAll));
      Task.bind("refresh change", this.proxy(this.renderCount));
      Task.fetch();
      setInterval(this.poll, 5*1000);
    },
    
    poll: function() {
      $.ajax({
        contentType: 'application/json',
        dataType: 'json',
  			type : 'GET',
        url : '/tasks/changes',
        success : function(data) {
          var taskid = [];
          Task.each(function(t) {
            taskid.push(t.id);
          });
          for (n in data.results) {
            if (-1 == ($.inArray(data.results[n].id, taskid))) {
              $.ajax({
                contentType: 'application/json',
                dataType: 'json',
                type: 'GET',
                url: '/tasks/'+data.results[n].id,
                success: function(data){
                  Task.create({
                    name: data[0].name,
                    done: data[0].done,
                    id: data[0].id
                  });
                }
              });
            }
          }
        }
      });
    },
    
    create: function(e){
      e.preventDefault();
      Task.create({
        name: this.input.val()
      });
      return this.input.val("");
    },

    addOne: function(task){
      var view;
      view = new Tasks({
        item: task
      });
      return this.items.append(view.render().el);
    },

    addAll: function(){
      return Task.each(this.proxy(this.addOne));
    },
    
    clear: function(){
       return Task.destroyDone();
    },
    
    renderCount: function(){
      var active, inactive;
      active = Task.active().length;
      this.count.text(active);
      inactive = Task.done().length;
      if (inactive) {
        return this.clear.show();
      } else {
        return this.clear.hide();
      }
    }

  });
  
  return new TaskApp({
    el: $("#tasks")
  });

});