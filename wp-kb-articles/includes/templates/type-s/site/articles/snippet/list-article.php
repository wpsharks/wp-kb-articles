<h3 class="-title">
	<a href="[permalink]">[title]</a>
</h3>

[if has_snippet]
	<div class="-snippet">
		...[snippet]...
	</div>
[endif]

<div class="-meta">
	<div class="-author">
		<span>by:</span> <a href="#" data-click-author="[author_id]">[author]</a>
	</div>

	[if tags]
		<div class="-tags">
			<span>tagged:</span> [tags]
		</div>
	[endif]

	[if comments_open || comments_number]
		<div class="-comments">
			[comments_number_text]
		</div>
	[endif]

	<div class="-date">
		[date]
	</div>

	<div class="-popularity">
		[popularity]
	</div>
</div>
