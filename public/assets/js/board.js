function TaskBoard(tasks){
    this.tasks = ko.observableArray(tasks);
}
ko.applyBindings(new TaskBoard(initialTasks), document.getElementById('board'));