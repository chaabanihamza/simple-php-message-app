<?php
// index.php

// تضمين ملف إعدادات قاعدة البيانات
// هذا الملف يقوم بإنشاء الاتصال بقاعدة البيانات في المتغير $conn
require_once 'config.php';

// تعريف المتغيرات وتهيئة القيم الفارغة
$name = $message = "";
$name_err = $message_err = "";

// معالجة بيانات النموذج عند الإرسال (عندما يتم النقر على زر "إرسال")
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // التحقق من صحة الاسم
    if (empty(trim($_POST["name"]))) {
        $name_err = "الرجاء إدخال اسم.";
    } else {
        $name = trim($_POST["name"]);
    }

    // التحقق من صحة الرسالة
    if (empty(trim($_POST["message"]))) {
        $message_err = "الرجاء إدخال رسالة.";
    } else {
        $message = trim($_POST["message"]);
    }

    // التحقق من أخطاء الإدخال قبل إدراج البيانات في قاعدة البيانات
    if (empty($name_err) && empty($message_err)) {
        // إعداد استعلام الإدراج
        // استخدام mysqli_real_escape_string لتطهير المدخلات ومنع حقن SQL
        $sql = "INSERT INTO messages (name, message) VALUES (?, ?)";

        // استخدام العبارات المُعدة (Prepared Statements) لأمان أفضل
        if ($stmt = mysqli_prepare($conn, $sql)) { // استخدام $conn هنا بدلاً من $link
            // ربط المتغيرات بالعبارة المُعدة كمعلمات
            mysqli_stmt_bind_param($stmt, "ss", $param_name, $param_message);

            // تعيين المعلمات
            $param_name = $name;
            $param_message = $message;

            // محاولة تنفيذ العبارة المُعدة
            if (mysqli_stmt_execute($stmt)) {
                // إعادة توجيه المتصفح لتحديث الصفحة بعد الإرسال الناجح
                header("location: index.php");
                exit();
            } else {
                echo "حدث خطأ ما. الرجاء المحاولة مرة أخرى لاحقًا.";
            }

            // إغلاق العبارة المُعدة
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl"> <!-- تحديد اللغة العربية والاتجاه من اليمين لليسار -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إرسال رسالة</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .wrapper {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin-bottom: 20px;
            text-align: right; /* محاذاة النص لليمين */
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* لضمان أن العرض يشمل الحشوة والحدود */
        }
        .form-group textarea {
            resize: vertical; /* السماح بتغيير حجم مربع النص عمودياً */
        }
        .form-group input[type="submit"] {
            background-color: #28a745; /* لون أخضر للزر */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .form-group input[type="submit"]:hover {
            background-color: #218838;
        }
        .error {
            color: #dc3545;
            font-size: 0.9em;
            display: block;
            margin-top: 5px;
        }
        .message-box {
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            text-align: right; /* محاذاة النص لليمين */
        }
        .message-box h4 {
            margin-top: 0;
            margin-bottom: 5px;
            color: #007bff;
        }
        .message-box p {
            margin-bottom: 5px;
        }
        .message-box small {
            color: #6c757d;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>إرسال رسالة</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>الاسم:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <span class="error"><?php echo $name_err; ?></span>
            </div>
            <div class="form-group">
                <label>الرسالة:</label>
                <textarea name="message" rows="4"><?php echo htmlspecialchars($message); ?></textarea>
                <span class="error"><?php echo $message_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" value="إرسال">
            </div>
        </form>
    </div>

    <div class="wrapper" style="margin-top: 20px;">
        <h2>الرسائل المخزنة</h2>
        <?php
        // استعلام SQL لجلب الرسائل، مرتبة حسب تاريخ الإنشاء تنازليًا
        $sql = "SELECT id, name, message, created_at FROM messages ORDER BY created_at DESC";

        // تنفيذ الاستعلام باستخدام $conn (كائن الاتصال)
        if ($result = mysqli_query($conn, $sql)) { // استخدام $conn هنا بدلاً من $link
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    echo "<div class='message-box'>";
                    echo "<h4>" . htmlspecialchars($row['name']) . "</h4>";
                    echo "<p>" . htmlspecialchars($row['message']) . "</p>";
                    echo "<small>تاريخ الإرسال: " . $row['created_at'] . "</small>";
                    echo "</div>";
                }
                // تحرير مجموعة النتائج
                mysqli_free_result($result);
            } else {
                echo "<p>لا توجد رسائل بعد.</p>";
            }
        } else {
            // عرض رسالة خطأ إذا فشل الاستعلام
            echo "خطأ: لم يتمكن من تنفيذ الاستعلام $sql. " . mysqli_error($conn); // استخدام $conn هنا
        }

        // إغلاق الاتصال بقاعدة البيانات
        // يجب إغلاق الاتصال مرة واحدة فقط في نهاية السكريبت
        mysqli_close($conn); // استخدام $conn هنا بدلاً من $link
        ?>
    </div>
</body>
</html>

