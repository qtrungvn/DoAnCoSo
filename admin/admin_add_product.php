<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .btn-custom {
            background-color: #6c757d;
            color: white;
            border-radius: 8px;
            padding: 10px;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background-color: #343a40;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center text-dark">Thêm Sản Phẩm</h2>
            <form action="admin_process_add.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên sản phẩm:</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả:</label>
                    <textarea id="description" name="description" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Giá (VNĐ):</label>
                    <input type="number" id="price" name="price" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Số lượng:</label>
                    <input type="number" id="stock" name="stock" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Hình ảnh:</label>
                    <input type="file" id="image" name="image" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Danh mục:</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="1">Đồ ăn</option>
                        <option value="2">Đồ uống</option>
                        <option value="3">Combo</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-custom w-100">Thêm sản phẩm</button>
            </form>
        </div>
    </div>
</body>
</html>
