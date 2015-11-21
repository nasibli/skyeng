function createGrid (cols, url, topBar, size) {

    var fields = [];

    for (var i=0; i < cols.length; i++) {

        fields.push ({name: cols[i].dataIndex, type: cols[i].type});

    }

    // create the Data Store
    var store = new Ext.data.JsonStore({
        root: 'res.data',
        totalProperty: 'res.total',
        remoteSort: true,
        fields: fields,
        // load using script tags for cross domain, if the data in on the same domain as
        // this page, an HttpProxy would be better
        proxy:new Ext.data.HttpProxy({
            method: 'GET',
            url: url
        })
    });
    store.setDefaultSort('id', 'desc');

    var grid = new Ext.grid.GridPanel({
        width: size ? size.width : 700,
        height:size ? size.height : 500,
        store: store,
        loadMask: true,

        // grid columns
        columns: cols,
        tbar: topBar,
        // paging bar on the bottom
        bbar: new Ext.PagingToolbar({
            pageSize: 50,
            store: store,
            beforePageText: 'Страница ',
            displayInfo: true,
            displayMsg: 'Отображены записи {0} - {1} из {2}',
            emptyMsg: "Нет данных для отображения"
        })
    });

    store.load();

    return grid;

}