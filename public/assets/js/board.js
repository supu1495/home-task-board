function TaskBoard(tasks, tags){
    var self = this;
    var csrf = csrfToken;

    self.tasks = ko.observableArray(tasks.map(function(task){ return new Task(task); }));
    self.tags = ko.observableArray(tags.map(function(tag){ return new Tag(tag); }));

    var savedTagId = tags.some(function(tag){ return tag.id === initialFilterTagId; }) ? initialFilterTagId : null;
    self.selectedTagId = ko.observable(savedTagId);
    self.sortKey = ko.observable(initialSortKey);
    self.isTagModalOpen = ko.observable(false);
    self.newTagName = ko.observable('');
    self.newTagColor = ko.observable('#4f6bed');

    self.selectTag = function(tag){ self.selectedTagId(tag.id); saveViewState(); };
    self.selectAll = function(){ self.selectedTagId(null); saveViewState(); };
    self.editingTaskId = editingTaskId;

    self.openEdit = function(task, event){
        if (event.target.closest('.check, form, a, button')){ return true; }
        window.location.href = endpoints.edit + '/' + task.id;
    };
    self.newSubtasks = ko.observableArray(
        initialNewSubtasks.map(function(title){ return ko.observable(title); })
    );

    self.addNewSubtask = function(){ self.newSubtasks.push(ko.observable('')); };
    self.removeNewSubtask = function(item){ self.newSubtasks.remove(item); };

     var compare = function(a, b, key){
        if (key === 'undone_first'){
            var diff = a.done() - b.done();
            if (diff !== 0){ return diff; }
        }
        var av = a.deadline_value;
        var bv = b.deadline_value;
        if (av === '' && bv === ''){ return 0; }
        if (av === ''){ return 1; }
        if (bv === ''){ return -1; }
        if (av === bv){ return 0; }
        var result = (av < bv) ? -1 : 1;
        return key === 'deadline_desc' ? -result : result;
    };

    self.filteredTasks = ko.computed(function(){
        var id = self.selectedTagId();
        var key = self.sortKey();
        var list = self.tasks();

        if (id !== null){
            list = list.filter(function(task){ return task.tag_id === id; });
        }
        return list.slice().sort(function(a, b){ return compare(a, b, key); });
    });

    var syncCsrfInputs = function(){
        var inputs = document.querySelectorAll('input[name="' + csrfKey + '"]');
        inputs.forEach(function(input){ input.value = csrf; });
    };

    var post = function(url, params){
        params[csrfKey] = csrf;
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams(params)
        })
        .then(function(res){
            if (res.status === 401){ window.location.reload(); throw new Error('unauthorized'); }
            if ( ! res.ok){ throw new Error('request failed'); }
            return res.json();
        })
        .then(function(data){
            if (data.csrf_token){
                csrf = data.csrf_token;
                syncCsrfInputs();
            }
            return data;
        });
    };

    var saveViewState = function(){
        post(endpoints.filter, {
            tag_id: self.selectedTagId() === null ? '' : self.selectedTagId(),
            sort: self.sortKey()
        }).catch(function(e){ console.error(e); });
    };

    self.sortKey.subscribe(saveViewState);

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
        syncCsrfInputs();
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