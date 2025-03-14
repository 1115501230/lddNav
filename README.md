# 小神龙开源导航系统-双端适配！
![image](https://github.com/user-attachments/assets/8b62f4b9-967c-4d76-befd-ae5de7b5786f)


## 环境要求
- PHP 7.4+ 
- SQLite3 扩展（可能需要手动开启）
- 现代浏览器（支持CSS3和ES6）

## 核心功能
- 后台管理为域名/admin
- 账号密码为admin+admin123

### 数据架构
- 三级分类体系：主分类 → 子分类 → 链接
- SQLite数据库存储结构：
  - primary_categories（主分类）
  - secondary_categories（子分类） 
  - links（具体链接）

### 前端特性
1. 响应式布局
   - 桌面端侧边栏导航（可折叠）
   - 移动端汉堡菜单（支持手势滑动）
   - 智能锚点定位（自动偏移导航栏高度）

2. 增强搜索功能
   - 本地模糊搜索（标题/描述）
   - 多引擎切换（百度/Google/Bing）
   - 实时结果过滤（300ms防抖）

3. 交互细节
   - SVG图标动态加载
   - 卡片悬停效果
   - 链接状态指示（活跃/禁用）
   - 图片加载异常处理

## 技术亮点

### 数据层
- 使用SQLite3轻量级数据库
- 自动过滤空分类（无链接不展示）
- 数据缓存机制（单次查询全量数据）

### 表现层
- 基于Tabler UI框架定制
- 移动端优化：
  - 侧边栏固定定位
  - 遮罩层过渡动画
  - 触控友好设计

### 前端增强
- 平滑滚动（scroll-behavior: smooth）
- 智能内容截断（text-truncate）
- 卡片圆角统一处理
- 图标异步加载（loading="lazy"）

## 部署说明
1. 上传到支持PHP的Web服务器
2. 确保navigation.db可写权限
3. 访问index.php即可使用



灵感来自OneNav书签管理工具

目前只有前端代码，后端代码-浏览器插件右键扩展制作中-书签导入制作中
