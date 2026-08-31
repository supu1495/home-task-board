function TaskBoard(tasks){
    this.tasks = ko.observableArray(tasks.map(function(task){ return new Task(task)}));
    this.toggleTask = function(task){
        fetch(endpoints.toggleTask, {
            method: 'POST', body: new URLSearchParams({ id: task.id })
        })
        .then(function(res){
            if ( ! res.ok){ throw new Error('toggle failed'); }
            return res.json();
        })
        .then(function(data){ task.done(data.done); })
        .catch(function(e){ console.error(e); })
    };
    this.toggleSubtask = function(subtask){
        fetch(endpoints.toggleSubtask, {
            method: 'POST', body: new URLSearchParams({ id: subtask.id })
        })
        .then(function(res){
            if ( ! res.ok){ throw new Error('toggle failed'); }
            return res.json();
        })
        .then(function(data){ subtask.done(data.done); })
        .catch(function(e){ console.error(e); })
    };
}
function Task(data){
    Object.assign(this, data);
    this.done = ko.observable(data.done);
    this.subtasks = data.subtasks.map(function(subtask){ return new SubTask(subtask); });
}
function SubTask(data){
    Object.assign(this, data);
    this.done = ko.observable(data.done);
}
ko.applyBindings(new TaskBoard(initialTasks), document.getElementById('board'));