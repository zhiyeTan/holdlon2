此目录用来存放组件视图文件
采用2级子目录的结构对组件视图进行管理，如：
component/dbname/tablename/viewname.tpl
component/list/article/matrix.tpl

调用组件的模版语法为：{component:firstDirName/secondDirName/viewName}

如需要调用数据，追加“|控制器类名”，如：
{component:list/article/matrix|appConArticleListCls}
该控制器同时作为数据接口