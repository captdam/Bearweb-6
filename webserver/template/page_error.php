<div class="pltr errorpage">
	<img src="/web/heihua.jpg" alt="黑化" />
	<div>
<?php if (substr(USERLANGUAGE,0,2) == 'en'): ?>
		<h2>I'll try to do this better next time...</h2>
		<p>Something gose wrong on the server. Miss Server and Miss Database have trouble processing this request; therefore, we are not able to access this content.</p>
		<p>Could you please help Miss Server on this issue? Error info provided below:</p>
<?php else: ?>
		<h2>服务器娘进入了傲娇模式。。。。。。</h2>
		<p>总之，由于某些不可抗因素，或许是你的操作导致服务器娘生气了，或是因为服务器娘的姐妹数据库娘做了什么，服务器娘现在进入了傲娇模式。因此，你将无法看到这个页面。</p>
		<p>下文是具体的错误信息。你可以尝试修复这个问题让服务器娘回到正常状态。正常来说只要不是持续性的错误，服务器娘是很宽宏大量的。</p>
<?php endif; ?>
		<section style="border-color: #FF0000; background-color: rgba(255,0,0,0.2);">
			<h2>HTTP ERROR - <?= http_response_code() ?></h2>
		</section>
		<section style="border-color: #FF0000; background-color: rgba(255,0,0,0.2);">
			<p><?= PAGEDATA['Info']['ErrorInfo'] ?></p>
			<p>
				Request ID: <?= TRANSACTIONID ?><br />
<?php if (substr(USERLANGUAGE,0,2) == 'en'): ?>
				<span class="info">Use this as a reference number if you need help.</span>
<?php else: ?>
				<span class="info">若需要技术支持，请提供此参考号</span>
<?php endif; ?>
			</p>
		</section>
<?php if (substr(USERLANGUAGE,0,2) == 'en'): ?>
		<h2>To deal with this:</h2>
		<p>There are some <del>useful</del> advices:</p>
		<ul>
			<li>Give Miss Server some time, try it later.</li>
			<li>Check the URL, do you have any typo mistake?</li>
 			<li>You should assume you can see the page, because you can the The Emperor's New Clothes.</li>
			<li>Use the dev tool / page inspector to peep Miss Server's secret garden.</li>
			<li>
			Casting a magic spell: "Admin command, sudo unlockpage -url=this -f". Sometimes this may not work.</li>
		</ul>
<?php else: ?>
		<h2>解决方案：</h2>
		<p>这里有一些<del>不太靠谱的</del>解决方案：</p>
		<ul>
			<li>服务器娘只是暂时傲娇了而已，等待一会兴许就好了。</li>
			<li>检查URL，也许不小心多打或者少打或者打错了一个字符。</li>
 			<li>假设你已经看见了这个页面，因为只有聪明的人才能看见这个页面。</li>
			<li>这个页面有服务器娘的小秘密，或许你可以通过使用网页开发者工具/HTML检视器来偷窥。</li>
			<li>在电脑前的地面上画上魔法阵，并大声喊出“使用系统管理员指令：sudo unlockpage -url=this -f”。虽然这个方法不太管用就是了。</li>
		</ul>
<?php endif; ?>
		
	</div>
</div>
