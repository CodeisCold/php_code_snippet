// 在console中动态添加js
var s = document.createElement('script');
s.setAttribute('src', 'http://xxx');
document.body.insertBefore(s, null); // 将 script 节点插入到 body 子节点列表末尾

// 用例：在 chrome 中覆写js时可能会遇到跨域问题（在 override 里导入的本地js文件会因为跨域无法使用）
// 可以通过更换本地js文件名，然后动态引入的方式解决