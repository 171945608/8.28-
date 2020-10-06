define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/goods/index' + location.search,
                    add_url: 'goods/goods/add',
                    edit_url: 'goods/goods/edit',
                    del_url: 'goods/goods/del',
                    multi_url: 'goods/goods/multi',
                    table: 'goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'images', title: __('Images'), events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'video', title: __('Video')},
                        // {field: 'cate_id', title: __('Cate_id')},
                        {field: 'cate_name', title: __('Cate_name')},
                        // {field: 'group_ids', title: __('Group_ids')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'oprice', title: __('Oprice'), operate:'BETWEEN'},
                        {field: 'delivery', title: __('Delivery')},
                        // {field: 'quotations', title: __('Quotations')},
                        // {field: 'new', title: __('New')},
                        // {field: 'hot', title: __('Hot')},
                        // {field: 'special', title: __('Special')},
                        {field: 'link', title: __('链接')},
                        {field: 'audit', title: __('Audit'), searchList: {"1":__('Audit 1'),"2":__('Audit 2'),"3":__('Audit 3')}, formatter: Table.api.formatter.normal},
                        // {field: 'audit_msg', title: __('Audit_msg')},
                        // {field: 'state', title: __('State'), searchList: {"1":__('State 1'),"0":__('State 0')}, formatter: Table.api.formatter.normal},
                        {field: 'store_id', title: __('Store_id')},
                        {field: 'home', title: __('Home'), searchList: {"1":__('Yes'),"0":__('No')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'cate', title: __('Cate'), searchList: {"1":__('Yes'),"0":__('No')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'operate', title: __('Operate'), table: table, buttons: [{
                                hidden:function(value){
                                    if(value.audit != 1){
                                        return true;
                                    }
                                },
                                name: 'audit',
                                text: __('审核'),
                                classname: 'btn btn-xs btn-info btn-dialog',
                                url: 'goods/goods/audit/'
                            }], events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        audit: function () {
            Controller.api.bindevent();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});