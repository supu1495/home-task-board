function TaskBoard(tasks, tags){
    var self = this;

    self.tasks = ko.observableArray(tasks.map(function(task){ return new Task(task); }));
    self.tags = ko.observableArray(tags.map(function(tag){ return new Tag(tag); }));
    var savedTagId = tags.some(function(tag){ return tag.id === initialFilterTagId; }) ? initialFilterTagId : null;
    self.selectedTagId = ko.observable(savedTagId);
    self.isTagModalOpen = ko.observable(false);
    self.newTagName = ko.observable('');
    self.newTagColor = ko.observable('#4f6bed');

    self.selectTag = function(tag){ self.selectedTagId(tag.id); saveFilter(tag.id); };
    self.selectAll = function(){ self.selectedTagId(null); saveFilter(null); };

    self.filteredTasks = ko.computed(function(){
        var id = self.selectedTagId();
        if (id === null){ return self.tasks(); }
        return self.tasks().filter(function(task){ return task.tag_id === id; });
    });

    var post = function(url, params){
        return fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: new URLSearchParams(params) })
            .then(function(res){
                if (res.status === 401){ window.location.reload(); throw new Error('unauthorized'); }
                if ( ! res.ok){ throw new Error('request failed'); }
                return res.json();
            });
    };

    var saveFilter = function(tagId){
        post(endpoints.filter, { tag_id: tagId === null ? '' : tagId })
            .catch(function(e){ console.error(e); });
    };

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

    var applyBoard = function(state){
        self.tags(state.tags.map(function(tag){ return new Tag(tag); }));
        self.tasks(state.tasks.map(function(task){ return new Task(task); }));

        var current = self.selectedTagId();
        var exists = state.tags.some(function(tag){ return tag.id === current; });
        if (current !== null && ! exists){ self.selectedTagId(null); }
    };

    self.toggleTask = function(task){
        post(endpoints.toggleTask, { id: task.id })
            .then(applyState)
            .catch(function(e){ console.error(e); });
    };

    self.toggleSubtask = function(subtask){
        post(endpoints.toggleSubtask, { id: subtask.id })
            .then(applyState)
            .catch(function(e){ console.error(e); });
    };

    self.openTagModal = function(){ self.isTagModalOpen(true); };
    self.closeTagModal = function(){ self.isTagModalOpen(false); };

    self.createTag = function(){
        var name = self.newTagName().trim();
        if (name === ''){ return; }
        post(endpoints.tagCreate, { name: name, color: self.newTagColor() })
            .then(function(state){
                applyBoard(state);
                self.newTagName('');
            })
            .catch(function(e){ console.error(e); });
    };

    self.updateTag = function(tag){
        post(endpoints.tagUpdate, { id: tag.id, name: tag.name(), color: tag.color() })
            .then(applyBoard)
            .catch(function(e){ console.error(e); });
    };

    self.deleteTag = function(tag){
        if ( ! window.confirm('このタグを削除しますか。タスクは残り、タグなしになります。')){ return; }
        post(endpoints.tagDelete, { id: tag.id })
            .then(applyBoard)
            .catch(function(e){ console.error(e); });
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

function Tag(data){
    this.id = data.id;
    this.name = ko.observable(data.name);
    this.color = ko.observable(data.color || '#4f6bed');
}

ko.applyBindings(new TaskBoard(initialTasks, initialTags), document.getElementById('app'));