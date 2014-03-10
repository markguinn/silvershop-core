<% include OrderReceiptStyle %>
<table id="Content" cellspacing="0" cellpadding="0" summary="Email Information">
	<% if Order %>
	<thead>
		<tr>
			<th scope="col" colspan="2">
				<% with Order %><h2><% _t('AccountPage.ss.ORDER','Order') %> $Reference ($Created.Month $Created.DayOfMonth, $Created.Year)</h2><% end_with %>
			</th>
		</tr>
	</thead>
	<% end_if %>
	<tbody>
		<tr>
			<td scope="row" colspan="2" class="typography">
				$PurchaseCompleteMessage
			</td>
		</tr>
		<% if Order %>
		<% loop Order %>
			<tr>
				<td>
					<% include Order %>
				</td>
			</tr>
		<% end_loop %>
		<% end_if %>
	</tbody>
</table>
