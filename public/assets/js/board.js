function TaskBoard(tasks){
    var self = this;
    self.tasks = ko.observableArray(tasks.map(function(task){ return new Task(task)}));
    var applyState = function(state){
        var task = null;
        var list = self.tasks();
        for (var i = 0; i < list.length; i++){
            if (list[i].id === state.id){ task = list[i]; break; }
        }
        if ( ! task){ return; }

        task.done(state.done);
        task.done_count(state.done_count);
        task.total_count(state.total_count);

        state.subtasks.forEach(function(newsub){
            task.subtasks.forEach(function(sub){
                if (sub.id === newsub.id){ sub.done(newsub.done); }
            });
        });
    };
    self.toggleTask = function(task){
        fetch(endpoints.toggleTask, {
            method: 'POST', body: new URLSearchParams({ id: task.id })
        })
        .then(function(res){
            if ( ! res.ok){ throw new Error('toggle failed'); }
            return res.json();
        })
        .then(function(data){ applyState(data); })
        .catch(function(e){ console.error(e); })
    };
    self.toggleSubtask = function(subtask){
        fetch(endpoints.toggleSubtask, {
            method: 'POST', body: new URLSearchParams({ id: subtask.id })
        })
        .then(function(res){
            if ( ! res.ok){ throw new Error('toggle failed'); }
            return res.json();
        })
        .then(function(data){ applyState(data); })
        .catch(function(e){ console.error(e); })
    };
}
function Task(data){
    Object.assign(this, data);
    this.done = ko.observable(data.done);
    this.done_count = ko.observable(data.done_count);
    this.total_count = ko.observable(data.total_count);
    this.percent = ko.computed(function(){
        return this.total_count() ? Math.round(this.done_count() / this.total_count() * 100) : 0;
    }, this);
    this.subtasks = data.subtasks.map(function(subtask){ return new SubTask(subtask); });
}
function SubTask(data){
    Object.assign(this, data);
    this.done = ko.observable(data.done);
}
ko.applyBindings(new TaskBoard(initialTasks), document.getElementById('board'));