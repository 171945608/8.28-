define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/shop/index' + location.search,
                    add_url: 'shop/shop/add',
                    edit_url: 'shop/shop/edit',
                    del_url: 'shop/shop/del',
                    multi_url: 'shop/shop/multi',
                    table: 'shop',
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
                        {field: 'username', title: __('Username')},
                        {field: 'nickname', title: __('Nickname')},
                        // {field: 'password', title: __('Password')},
                        // {field: 'salt', title: __('Salt')},
                        // {field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'email', title: __('Email')},
                        {field: 'company', title: __('公司名称')},
                        {field: 'address', title: __('公司地址')},
                        // {field: 'loginfailure', title: __('Loginfailure')},
                        // {field: 'logintime', title: __('Logintime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'loginip', title: __('Loginip')},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'token', title: __('Token')},
                        // {field: 'status', title: __('Status')},
                        // {field: 'business_cate_id', title: __('Business_cate_id')},
                        {field: 'business_cate_name', title: __('Business_cate_name')},
                        {field: 'idcard_ps', title: __('Idcard_ps'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'idcard_rs', title: __('Idcard_rs'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'business_license', title: __('Business_license'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'qualification', title: __('Qualification'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'audit_state', title: __('Audit_state'), searchList: {"10":__('Audit_state 10'),"20":__('Audit_state 20'),"30":__('Audit_state 30')}, formatter: Table.api.formatter.normal},
                        {field: 'forbid_type', title: __('Forbid_type'), searchList: {"0":__('Forbid_type 0'),"1":__('Forbid_type 1'),"2":__('Forbid_type 2'),"3":__('Forbid_type 3'),"4":__('Forbid_type 4')}, formatter: Table.api.formatter.normal},
                        // {field: 'audit_msg', title: __('Audit_msg')},
                        {field: 'operate', title: __('Operate'), table: table, buttons: [{
                                hidden:function(value){
                                    if(value.audit_state!=10){
                                        return true;
                                    }
                                },
                                name: 'audit',
                                text: __('审核'),
                                classname: 'btn btn-xs btn-info btn-dialog',
                                url: 'shop/shop/audit'
                            }, {
                                hidden:function(value){
                                    if(value.audit_state != 30 || value.forbid_type != 0){
                                        return true;
                                    }
                                },
                                name: 'forbid',
                                text: __('封禁'),
                                classname: 'btn btn-xs btn-info btn-dialog',
                                url: 'shop/shop/forbid'
                            }, {
                                hidden:function(value){
                                    if(value.audit_state != 30){
                                        return true;
                                    }
                                },
                                name: 'oss',
                                text: __('OSS'),
                                classname: 'btn btn-xs btn-info btn-dialog',
                                url: 'shop/shop/oss'
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
        forbid: function () {
            Controller.api.bindevent();
        },
        oss: function () {
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