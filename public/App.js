function createTeacherTab () {
    var gridTeachers = createTeacherGrid('teachers/list');
    var teacherWindow = createTeacherWindow(function (){gridTeachers.getStore().load()});
    var teacherPupilAddWindow = createTeacherPupilsWindow(true, 'teacherPupilAddWindow');
    var teacherPupilViewWindow = createTeacherPupilsWindow(false, 'teacherPupilViewWindow');
    var teacherBar = createTeacherBar(gridTeachers, ['add','add_pupils','view_pupils','search', 'only_april'], teacherWindow,
        teacherPupilAddWindow, teacherPupilViewWindow);
    var tbar = gridTeachers.getTopToolbar();
    for (var i=0; i<teacherBar.length; i++) {
        tbar.add(teacherBar[i]);
    }
    return gridTeachers;
}

function createPupilTab () {
    var gridPupils = createPupilGrid('pupils/list');
    var pupilWindow = createPupilWindow(function (){gridPupils.getStore().load()});
    var pupilBar = createPupilBar(gridPupils,['add','pupil_search', 'date_birth'],pupilWindow);
    var tbar = gridPupils.getTopToolbar();
    for (var i=0; i<pupilBar.length; i++) {
        tbar.add(pupilBar[i]);
    }
    gridPupils.doLayout();
    return gridPupils;
}

function createTeacherGrid(url){
    function renderGender(value, p, record){

        return record.data.gender == 1 ? 'Мужской' : 'Женский';

    }

    var teacherCols = [{
        id: 'id',
        header: "ID",
        dataIndex: 'id',
        width: 80,
        sortable: true,
        type: 'int'
    },{
        header: "Имя",
        dataIndex: 'name',
        width: 100,
        sortable: true,
        type: 'string'
    },{
        header: "Пол",
        dataIndex: 'gender',
        width: 70,
        renderer: renderGender,
        type: 'string'
    },{
        header: "Телефон",
        dataIndex: 'phone',
        width: 100,
        type: 'string'
    },{
        header: "Ученики",
        dataIndex: 'pupil_count',
        width: 70,
        type: 'int'
    }];

    return createGrid(teacherCols, 'teachers/list', []);
}

function createTeacherWindow(addHandler) {
    var teacherWindow = new Ext.Window({
        applyTo: 'teacherWindow',
        layout:'fit',
        width:500,
        height:175,
        closeAction:'hide',
        title: 'Добавление учителя',

        items: new Ext.FormPanel({
            labelWidth: 80, // label settings here cascade unless overridden
            itemId: 'teacherFrm',
            frame:true,
            bodyStyle:'padding:5px 5px 0',
            width: 490,
            defaults: {
                width: 380
            },
            defaultType: 'textfield',

            items: [{
                fieldLabel: 'Имя',
                name: 'name',
                allowBlank:false
            },{
                fieldLabel: 'Телефон',
                name: 'phone',
                allowBlank:false
            }, {
                xtype: 'combo',
                itemId: 'comboGen',
                fieldLabel: 'Пол',
                name: 'gender',
                mode: 'local',
                displayField: 'name',
                valueField: 'id',
                store: new Ext.data.ArrayStore({
                    idIndex: 0,
                    autoDestroy: true,
                    fields: [
                        {name: 'id', type: 'int'},
                        {name: 'name', type: 'string'}
                    ],
                    data: [[1, 'Мужской'], [2, 'Женский']]
                }),
                triggerAction: 'all',
                value: 1
            }
            ]
        }),

        buttons: [{
            text:'Добавить',
            handler: function () {

                var frm = teacherWindow.getComponent('teacherFrm');
                frm.getForm().submit({
                    clientValidation: true,
                    url: 'teachers/save',
                    params: {genderId: frm.getComponent('comboGen').getValue()},
                    success: function(form, action) {
                        addHandler();
                        teacherWindow.hide();
                    },
                    failure: function(form, action) {
                        switch (action.failureType) {
                            case Ext.form.Action.CLIENT_INVALID:
                                Ext.Msg.alert('Ошибка', 'Корректно заполните поля формы');
                                break;
                            case Ext.form.Action.SERVER_INVALID:
                                Ext.Msg.alert('Ошибка', action.result.errors.join("\n"));
                                break;
                        }
                    }
                })

            }
        },{
            text: 'Отменить',
            handler: function(){
                teacherWindow.hide();
            }
        }]
    });
    return teacherWindow;
}

function createTeacherPupilsWindow(edit, applyTo) {
    var gp = createPupilGrid('pupils/list');
    var pb = createPupilBar(gp,['pupil_search', 'date_birth']);
    var tb = gp.getTopToolbar();
    for (var i=0; i<pb.length; i++) {
        tb.add(pb[i]);
    }

    var teacherPupilAddWindow =  new Ext.Window({
        applyTo: applyTo,
        layout:'fit',
        teacherId: 0,
        width:620,
        height:460,
        closeAction:'hide',
        title: edit ? 'Добавление ученика учителю' : 'Просмотр учеников',
        loadPupils: function () {
            var pGrid = teacherPupilAddWindow.items.first();
            pGrid.getStore().setBaseParam('id', this.teacherId);
            pGrid.getStore().load();
        },

        items: gp,
        buttons: !edit ? [] : [{
            text:'Добавить учеников',
            handler: function () {
                var pGrid = teacherPupilAddWindow.items.first();
                var records = pGrid.getSelectionModel().getSelections();
                if (!pGrid.getSelectionModel().hasSelection()) {
                    Ext.Msg.alert('Ошибка', 'Вы не выбрали ученика');
                }
                var params = {id: teacherPupilAddWindow.teacherId, pupils: {}};
                for (var i = 0; i<records.length; i++) {
                    params.pupils[records[i].data.id] = records[i].data.date_birth[4] == '4' ? 1 : 0;
                }

                Ext.Ajax.request({
                    url: 'teachers/add-pupils',
                    params: 'data = {\"data\":' + Ext.encode(params) + '}',
                    method: 'POST',
                    success: function(response, opts) {
                        var resp = Ext.decode(response.responseText);
                        if (resp.success) {
                            teacherPupilAddWindow.hide();
                        } else {
                            Ext.Msg.alert('Ошибка',resp.errors);
                        }
                    },
                    failure: function(response, opts) {
                        Ext.Msg.alert('Ошибка','Внутрення ошибка сервера.');
                    }
                });

            }
        },{
            text: 'Отменить',
            handler: function(){
                teacherPupilAddWindow.hide();
            }
        }]
    });

    return teacherPupilAddWindow;
}

function createTeacherBar(gridTeachers, barItems, teacherWindow, teacherPupilAddWindow, teacherPupilViewWindow) {
    var items = [];
    for (var i=0; i<barItems.length; i++) {
        switch (barItems[i]) {
            case 'add':
                items.push({
                    xtype: 'button',
                    text: 'Добавить учителя',
                    listeners: {
                        click: function () {
                            teacherWindow.show();
                        }
                    }
                });
                break;
            case 'add_pupils':
                items.push({
                    xtype: 'button',
                    text: 'Добавить учеников',
                    listeners: {
                        click: function () {
                            if (!gridTeachers.getSelectionModel().hasSelection()) {
                                Ext.Msg.alert('', 'Выберите учителя в таблице');
                                return;
                            }
                            var rec = gridTeachers.getSelectionModel().getSelected();
                            teacherPupilAddWindow.teacherId = rec.data['id'];
                            teacherPupilAddWindow.show();
                        }
                    }
                });
                break;
            case 'view_pupils':
                items.push({
                    xtype: 'button',
                    text: 'Посмотреть учеников',
                    listeners: {
                        click: function () {
                            if (!gridTeachers.getSelectionModel().hasSelection()) {
                                Ext.Msg.alert('', 'Выберите учителя в таблице');
                                return;
                            }
                            var rec = gridTeachers.getSelectionModel().getSelected();
                            teacherPupilViewWindow.teacherId = rec.data['id'];
                            teacherPupilViewWindow.show();
                            teacherPupilViewWindow.loadPupils();
                        }
                    }
                });
                break;
            case 'search':
                items.push('  Поиск: ');
                items.push(' ');
                items.push({
                    xtype: 'textfield',
                    emptyText: 'Имя учителя',
                    width: 200,
                    enableKeyEvents: true,
                    listeners: {
                        keypress: function (cmp, e) {
                            if (e.keyCode==13) {
                                gridTeachers.getStore().setBaseParam('search', cmp.getValue());
                                gridTeachers.getStore().load();
                            }
                        }
                    }
                });
                break;
            case 'only_april':
                items.push(' Апрель ');
                items.push({
                    xtype: 'checkbox',
                    listeners: {
                        check: function (cmp, checked) {
                            gridTeachers.getStore().setBaseParam('only_april', checked ? 1 : 0);
                            gridTeachers.getStore().load();
                        }
                    }
                });
                break;
        }
    }
   return items;
}

function createPupilGrid (url){
    var levels = {
        1 : 'A1',
        2 : 'A2',
        3 : 'B1',
        4 : 'B2',
        5 : 'C1',
        6 : 'C2'
    }
    function renderLevel (value, p, record) {
        return levels[record.data.level_id]
    }
    var pupilCols = [{
        id: 'id',
        header: "ID",
        dataIndex: 'id',
        width: 80,
        sortable: true,
        type: 'int'
    },{
        header: "Имя",
        dataIndex: 'name',
        width: 120,
        sortable: true,
        type: 'string'
    },{
        header: "Email",
        dataIndex: 'email',
        width: 200,
        type: 'string'
    },{
        header: "Уровень",
        dataIndex: 'level_id',
        width: 80,
        type: 'int',
        renderer: renderLevel
    },{
        header: "Дата рождения",
        dataIndex: 'date_birth',
        sortable: true,
        width: 100,
        type: 'string'
    }];

    return createGrid(pupilCols, url, [' ']/*forWindow ? {width: 606, height: forView ? 428 : 395} : null*/);
}

function createPupilWindow(addHandler) {

    var pupilWindow = new Ext.Window({
        applyTo: 'pupilWindow',
        layout:'fit',
        width:500,
        height:200,
        closeAction:'hide',
        title: 'Добавление ученика',

        items: new Ext.FormPanel({
            labelWidth: 100, // label settings here cascade unless overridden
            itemId: 'pupilFrm',
            frame:true,
            bodyStyle:'padding:5px 5px 0',
            width: 490,
            defaults: {
                width: 360
            },
            defaultType: 'textfield',

            items: [{
                fieldLabel: 'Имя',
                name: 'name',
                allowBlank:false
            },{
                fieldLabel: 'Email',
                name: 'email',
                vtype: 'email',
                allowBlank:false
            }, {
                xtype: 'combo',
                itemId: 'comboLev',
                fieldLabel: 'Уровень',
                name: 'level',
                mode: 'local',
                displayField: 'name',
                valueField: 'id',
                store: new Ext.data.ArrayStore({
                    idIndex: 0,
                    autoDestroy: true,
                    storeId: 'myStore',
                    fields: [
                        {name: 'id', type: 'int'},
                        {name: 'name', type: 'string'}
                    ],
                    data: [[1, 'A1'], [2, 'A2'], [3, 'B1'], [4, 'B2'], [5, 'C1'], [6, 'C2']]
                }),
                triggerAction: 'all',
                value: 1
            },{
                xtype: 'datefield',
                fieldLabel: 'Дата рождения',
                id: 'date_birth',
                allowBlank: false,
                format: 'd.m.Y'
            }
            ]
        }),

        buttons: [{
            text:'Добавить',
            handler: function () {

                var frm = pupilWindow.getComponent('pupilFrm');
                frm.getForm().submit({
                    clientValidation: true,
                    url: 'pupils/save',
                    params: {level_id: frm.getComponent('comboLev').getValue()},
                    success: function(form, action) {
                        addHandler();
                        pupilWindow.hide();
                    },
                    failure: function(form, action) {
                        switch (action.failureType) {
                            case Ext.form.Action.CLIENT_INVALID:
                                Ext.Msg.alert('Ошибка', 'Корректно заполните поля формы');
                                break;
                            case Ext.form.Action.SERVER_INVALID:
                                Ext.Msg.alert('Ошибка', action.result.errors.join("\n"));
                                break;
                        }


                        Ext.Msg.alert('Ошибка',action.result.errors);
                    }
                })

            }
        },{
            text: 'Отменить',
            handler: function(){
                pupilWindow.hide();
            }
        }]
    });
    return pupilWindow;
}

function createPupilBar(gridPupils, barItems, pupilWindow){

    var items = [];

    for (var i=0; i<barItems.length; i++) {
        var barItem = barItems[i];
        switch (barItem) {
            case 'add':
                items.push({
                    xtype: 'button',
                    text: 'Добавить',
                    listeners: {
                        click: function () {
                            pupilWindow.show();
                        }
                    }
                })
                break;
            case 'pupil_search':
                items.push({
                    xtype: 'textfield',
                    //id: 'pupil_search',
                    emptyText: 'Имя ученика',
                    width: 200,
                    enableKeyEvents: true,
                    listeners: {
                        keypress: function (cmp, e) {
                            if (e.keyCode==13) {
                                gridPupils.getStore().setBaseParam('search', cmp.getValue());
                                gridPupils.getStore().load();
                            }
                        }
                    }
                });
                break;
            case 'date_birth':
                items.push(' Дата рождения с: ');
                items.push(' ');
                items.push({
                    xtype: 'datefield',
                    //id: 'date_birth_from',
                    emptyText: 'Дата рождения',
                    format: 'd.m.Y',
                    width: 110,
                    listeners: {
                        change: function (cmp, newVal, oldVal) {
                            gridPupils.getStore().setBaseParam('date_birth_from', newVal ? newVal.dateFormat('d.m.Y') : '');
                            gridPupils.getStore().load();
                        }
                    }
                });
                items.push(' по: ');
                items.push({
                    xtype: 'datefield',
                    //id: 'date_birth_to',
                    emptyText: 'Дата рождения',
                    format: 'd.m.Y',
                    width: 110,
                    listeners: {
                        change: function (cmp, newVal, oldVal) {
                            gridPupils.getStore().setBaseParam('date_birth_to', newVal ? newVal.dateFormat('d.m.Y') : '');
                            gridPupils.getStore().load();
                        }
                    }
                });
                break;
        }
    }
    return items;
}

function createPupilPairTab () {

    var store = new Ext.data.JsonStore({
        fields: [
            {name: 'pupil_id', type: 'int'},
            {name: 'name',     type: 'String'}
        ]
    });

    var grid = new Ext.grid.GridPanel({
        store: store,
        columns: [
            {
                header   : 'ID',
                width    : 90,
                dataIndex: 'pupil_id'
            },
            {
                header   : 'Имя',
                width    : 200,
                sortable : true,
                dataIndex: 'name'
            }

        ],
        stripeRows: true,
        height: 350,
        width: 600,
        title: 'Учителя',
        tbar: [
             {
                xtype: 'button',
                text: 'Обновить',
                listeners: {
                    click: function () {
                        Ext.Ajax.request({
                            url: 'teachers/max-pairs',
                            success: function(response, opts) {
                                var resp = Ext.decode(response.responseText);
                                if (resp.res) {
                                    store.loadData(resp.res.pupils);
                                    grid.setTitle('Учителя: ' + resp.res.teacher1.name + '(' + resp.res.teacher1.id + ')'
                                        + ', ' + resp.res.teacher2.name + '(' + resp.res.teacher2.id + ')' + '  ' + resp.res.pupils.length + ' общих учеников');
                                } else {
                                    Ext.Msg.alert('','Нет общих учеников');
                                }
                            },
                            failure: function(response, opts) {
                                Ext.Msg.alert('Ошибка','Внутрення ошибка сервера.');
                            }
                        });
                    }
                }
            }
        ]
    });

    return grid;

}