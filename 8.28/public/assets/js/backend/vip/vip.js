define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vip/vip/index' + location.search,
                    add_url: 'vip/vip/add',
                    edit_url: 'vip/vip/edit',
                    del_url: 'vip/vip/del',
                    multi_url: 'vip/vip/multi',
                    table: 'user',
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
                        // {field: 'group_id', title: __('Group_id')},
                        // {field: 'username', title: __('Username')},
                        {field: 'nickname', title: __('Nickname')},
                        // {field: 'password', title: __('Password')},
                        // {field: 'salt', title: __('Salt')},
                        // {field: 'email', title: __('Email')},
                        {field: 'mobile', title: __('Mobile')},
                        {
                            field: 'avatar',
                            title: __('Avatar'),
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        // {field: 'level', title: __('Level')},
                        // {field: 'gender', title: __('Gender')},
                        // {field: 'birthday', title: __('Birthday'), operate:'RANGE', addclass:'datetimerange'},
                        // {field: 'bio', title: __('Bio')},
                        // {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        // {field: 'score', title: __('Score')},
                        // {field: 'successions', title: __('Successions')},
                        // {field: 'maxsuccessions', title: __('Maxsuccessions')},
                        // {field: 'prevtime', title: __('Prevtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'logintime', title: __('Logintime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'loginip', title: __('Loginip')},
                        // {field: 'loginfailure', title: __('Loginfailure')},
                        // {field: 'joinip', title: __('Joinip')},
                        // {field: 'jointime', title: __('Jointime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'token', title: __('Token')},
                        // {field: 'status', title: __('Status')},
                        {
                            field: 'forbid_type',
                            title: __('Forbid_type'),
                            searchList: {
                                "0": __('Forbid_type 0'),
                                "1": __('Forbid_type 1'),
                                "2": __('Forbid_type 2'),
                                "3": __('Forbid_type 3'),
                                "4": __('Forbid_type 4')
                            },
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'forbid_end_time',
                            title: __('Forbid_end_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        // {field: 'verification', title: __('Verification')},
                        // {field: 'openid', title: __('Openid')},
                        // {field: 'tv', title: __('Tv')},
                        // {field: 'id_auth', title: __('Id_auth'), searchList: {"0":__('Id_auth 0'),"1":__('Id_auth 1'),"2":__('Id_auth 2')}, formatter: Table.api.formatter.normal},
                        // {field: 'realname', title: __('Realname')},
                        // {field: 'idcard_no', title: __('Idcard_no')},
                        // {field: 'idcard_ps', title: __('Idcard_ps')},
                        // {field: 'idcard_rs', title: __('Idcard_rs')},
                        // {field: 'vip_auth', title: __('Vip_auth'), searchList: {"0":__('Vip_auth 0'),"1":__('Vip_auth 1'),"2":__('Vip_auth 2')}, formatter: Table.api.formatter.normal},
                        {field: 'vipname', title: __('Vipname')},
                        {field: 'viplink', title: __('Viplink')},
                        {
                            field: 'operate', title: __('Operate'), table: table, buttons: [{
                                name: 'forbid',
                                text: __('封禁'),
                                classname: 'btn btn-xs btn-info btn-dialog',
                                url: 'user/id_auth/forbid'
                            }], events: Table.api.events.operate, formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        forbid: function () {
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