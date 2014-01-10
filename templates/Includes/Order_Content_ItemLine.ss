<tr class="itemRow $EvenOdd $FirstLast">
	<td width="100">
		<div class="image center">
		<% if $Image %>
				<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">
					<img src="<% with $Image.setWidth(45) %>$Me.AbsoluteURL<% end_with %>" alt="$Buyable.Title" />
				</a>
			</div>
		<% else %>
			<img src="/app/images/no-product-photo.jpg" />
		<% end_if %>
		</div>
	</td>
	<td class="product title" scope="row">
		<h3>
		<% if Link %>
			<a href="$Link" title="<% sprintf(_t("READMORE","View &quot;%s&quot;"),$Title) %>">$TableTitle</a>
		<% else %>
			$TableTitle
		<% end_if %>
		</h3>
		<% if SubTitle %><p class="subtitle">$SubTitle</p><% end_if %>
	</td>
	<td class="center unitprice">$UnitPrice.Nice</td>
	<td class="center quantity">$Quantity</td>
	<td class="right total">$Total.Nice</td>
</tr>