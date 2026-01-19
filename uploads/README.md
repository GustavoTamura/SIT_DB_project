# 电影海报图片上传指南

## 图片存储方式
- ✅ 图片路径存在数据库（推荐）
- ✅ 图片文件存在服务器 `uploads/movies/` 目录

## 数据库字段
```sql
ALTER TABLE movie ADD COLUMN poster_image VARCHAR(255) DEFAULT NULL;
```

## 支持的格式
- JPG/JPEG
- PNG
- GIF
- WebP

## 文件大小限制
最大 5MB

## 使用方法

### 1. 在 Admin 页面上传
1. 访问 http://localhost/SIT_DB_project/admin.php
2. 编辑或添加电影
3. 选择 "Poster Image" 文件
4. 保存

### 2. 手动上传
1. 将图片放到 `uploads/movies/` 目录
2. 在数据库中更新：
```sql
UPDATE movie SET poster_image = 'uploads/movies/your_image.jpg' WHERE movie_id = 1;
```

## 图片将显示在
- ✅ 首页电影卡片
- ✅ Admin 管理列表
- ✅ 预订页面（可以添加）

## 注意事项
- 图片会自动重命名为 `{movie_id}_{timestamp}.{ext}`
- 旧图片不会自动删除（需要手动清理）
- 确保 `uploads/movies/` 目录有写入权限
