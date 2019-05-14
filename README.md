# qFrame
# 一个结合了前/后端框架的综合框架，可以直接用于开发
------
php框架为thinkphp，  
模块划分：  
admin模块：基于adminlte开发，包含Bootstrap，JS框架为JQuery，同时引入了众多前端组件。  
index模块：基于framework7和vue开发。  
common模块：包含公共模型和公共方法，其他所有模块的控制器都继承自common/controllor/Base。  
api模块：包含ajax调用的所有接口。  
  
### 使用方法
web目录指向www即可，无需指向www/public，  
.htaccess文件实现将指向根目录的请求自动转向public目录，  
需要根据.env.example文件生成自己环境的.env文件，

欢迎issue
