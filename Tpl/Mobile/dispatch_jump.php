<include file="PC:Public:header" />
<body>

<div class="container">
	<dl class="palette palette-peter-river">
		<h3 class="demo-panel-title"><center>爱情银行</center></h3>
	 </dl>
	<div class="demo-headline">
		<h1 class="demo-logo">
			<div class="system-message">
			<present name="message">
			:)<br>
			<p class="success"><?php echo($message); ?></p>
			<else/>
			:(<br>
			<p class="error"><?php echo($error); ?></p>
			</present>
			<p class="detail"></p>
			<p class="jump">
			页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> <br>等待时间： <b id="wait"><?php echo($waitSecond); ?></b>
			</p>
			</div>
			<script type="text/javascript">
			(function(){
			var wait = document.getElementById('wait'),href = document.getElementById('href').href;
			var interval = setInterval(function(){
				var time = --wait.innerHTML;
				if(time <= 0) {
					location.href = href;
					clearInterval(interval);
				};
			}, 1000);
			})();
			</script>
		</h1>
	</div> <!-- /demo-headline -->
</div> <!-- /container -->

		
		
	
<include file="PC:Public:footer" />
