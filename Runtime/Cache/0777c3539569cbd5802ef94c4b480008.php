<?php if (!defined('THINK_PATH')) exit();?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>爱情银行</title>
<!-- Bootstrap -->   <link href="__PUBLIC__/css/bootstrap.min.css" rel="stylesheet" media="screen">

   <style type="text/css">
      body {
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
      }

      .form-signin {
        max-width: 300px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signin .form-signin-heading,
      .form-signin .checkbox {
        margin-bottom: 10px;
      }
      .form-signin input[type="text"],
      .form-signin input[type="password"] {
        font-size: 16px;
        height: auto;
        margin-bottom: 15px;
        padding: 7px 9px;
      }

    </style>
</head>


<body>

	<div class="hero-unit">
	<h1 class="text-center"><?php echo ($View_SOFTNAME); ?></h1>
	<p class="text-center">by co8bit <?php echo ($View_VERSION); ?></p>
	<p align="center">
		<a class="btn btn-primary btn-large" href="<?php echo U('Index/login');?>">
		进入	</a>	
	</p>
	</div>
	
	
		<div class="container">
			<div class="row">
				<center>
					<h3 class="footer-title">关于</h3>
					<p>您是不是喜欢这个应用呢？<br>
					  如果喜欢的话可以关注我们的微博：<a href="http://weibo.com/u/3947676737" target="_blank">新浪微博</a><br>
					  您也可以赞助我们：
						<a class="footer-brand" href="https://me.alipay.com/co8bit" target="_blank">
							<img src="__PUBLIC__/FlatUI/images/donate_link.png" alt="donate_link">
						</a>
					</p>
				</center>
			</div>
		</div>


<!-- Bootstrap -->    <script src="__PUBLIC__/js/bootstrap.min.js"></script>
</body>
</html>