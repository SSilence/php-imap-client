<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>PHP Developer Tests</title>
	<link rel="shortcut icon" type="image/x-icon" href="http://php.net/favicon.ico">
	<style>
		body {
			margin: 0 auto;
			background-color: #F2F2F2;
			font-weight: 400;
			font-size: 14px;
			font-family: "Fira Mono", "Source Code Pro", monospace;
		}

		header {
			background-color: #8892BF;
			border-bottom: 4px solid #4F5B93;
			width: 100%;
			height: 48px;
		}

		a {
			text-decoration: none;
		}

		.logo {
			float: left;
			margin: 10px 10px 10px 0;
			width: 48px;
			background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="48" height="24"><g transform="matrix(0.25058516 0 0 0.25058516 -11.184032 -111.63545)matrix(2 0 0 2 -140.15749 -493.22304)"><path d="m100 479.5 14.2 0c4.2 0 7.2 1.2 9.1 3.6 1.9 2.4 2.5 5.6 1.9 9.7-0.2 1.9-0.8 3.7-1.6 5.5-0.8 1.8-1.9 3.4-3.4 4.9-1.8 1.8-3.7 3-5.7 3.5-2 0.5-4.1 0.7-6.3 0.7l-6.4 0-2 10.1-7.4 0 7.6-38 0 0m6.2 6-3.2 15.9c0.2 0 0.4 0.1 0.6 0.1 0.2 0 0.5 0 0.7 0 3.4 0 6.2-0.3 8.5-1 2.3-0.7 3.8-3.3 4.6-7.7 0.6-3.7 0-5.8-1.9-6.4-1.9-0.6-4.2-0.8-7-0.8-0.4 0-0.8 0.1-1.2 0.1-0.4 0-0.7 0-1.1 0l0.1-0.1"/><path d="m133.5 469.4 7.3 0-2.1 10.1 6.6 0c3.6 0.1 6.3 0.8 8.1 2.2 1.8 1.4 2.3 4.1 1.6 8.1l-3.6 17.6-7.4 0 3.4-16.9c0.4-1.8 0.2-3-0.3-3.8-0.6-0.7-1.8-1.1-3.7-1.1l-5.9-0.1-4.3 21.8-7.3 0 7.6-38.1 0 0"/><path d="m162.8 479.5 14.2 0c4.2 0 7.2 1.2 9.1 3.6 1.9 2.4 2.5 5.6 1.9 9.7-0.2 1.9-0.8 3.7-1.6 5.5-0.8 1.8-1.9 3.4-3.4 4.9-1.8 1.8-3.7 3-5.7 3.5-2 0.5-4.1 0.7-6.3 0.7l-6.4 0-2 10.1-7.4 0 7.6-38 0 0m6.2 6-3.2 15.9c0.2 0 0.4 0.1 0.6 0.1 0.2 0 0.5 0 0.7 0 3.4 0 6.2-0.3 8.5-1 2.3-0.7 3.8-3.3 4.6-7.7 0.6-3.7 0-5.8-1.9-6.4-1.9-0.6-4.2-0.8-7-0.8-0.4 0-0.8 0.1-1.2 0.1-0.4 0-0.7 0-1.1 0l0.1-0.1"/></g></svg>') no-repeat;
			height: 24px;
		}

		ul.dev {
			float: left;
			color: #FFF;
			text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
			font-size: 16px;
			padding: 0;
			list-style: outside none none;
		}

		ul.dev li {
			display: inline;
			float: left;
		}

		ul.dev li a {
			padding: 17px;
			/*color: #E2E4EF;*/
			color: #fff;
			text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
		}

		ul.dev li a:hover,
		ul.dev li a.active {
			color: #fff;
		}

		ul.dev li a.active {
			background-color: #4F5B93;
		}

		h1 {
			color: #793862;
			font-weight: 500;
			font-size: 1.25rem;
			line-height: 3rem;
			overflow: hidden;
			text-rendering: optimizelegibility;
			border-bottom: 1px dotted #793862;
			padding: 0 10px;
			width: 100%;
		}

		.container {
			margin: 0 auto;
			width: 80%;
			min-width: 800px;
		}

		pre {
			box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.15) inset;
			border-radius: 0 0 2px 2px;
			padding: 10px;
			word-spacing: -0.125rem;
			background-color: #FFF;
			border-color: #D9D9D9;
			width: 100%;
			min-height: 200px;
			overflow-x: auto;
		}

		footer {
			text-align: right;
			color: #777;
			margin: 20px auto;
		}

		footer a {
			color: inherit;
		}

		footer a:hover {
			color: #AE508D;
		}
	</style>
</head>
<body>
<header>
	<div class="container">
		<div class="logo"></div>
		<ul class="dev">
			<li><a href="">Developer Tests</a></li>
		</ul>
	</div>
</header>
<div class="container">
	<h1>Output</h1>
	<pre><?php
		/**
		 * @var mixed $output It must be configured like single output of the controller
		 */
		print_r($output);
		?>
	</pre>
</div>
<footer>
	<div class="container">
		<a href="http://github.com/natanfelles/php-view" target="_blank">PHP Developer Tests View</a> by
		<a href="http://natanfelles.github.io" target="_blank">Natan Felles</a>
	</div>
</footer>
</body>
</html>