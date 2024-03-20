<div class="product-comparator">

        <table>
                <tr>
                        <td><b>Statut mise à jour</b></td>
                        <td>{$updateStatus}</td>
                </tr>

                <tr>
                        <td><b>Date mise à jour</b></td>
                        <td>{$updateDate}</td>
                </tr>
                
                <tr>
			<td><b>Prix minimal</b></td>
                        <td>{$minimalPrice}</td>
                </tr>
        </table>
        


	<table>
	<tr>
		<th>Vendeur</td>
		<th>Détails</td>
		<th>Prix</td>
		<th>Prix + livraison</td>

	</tr>
	{foreach $comparators as $comparator}

		<tr>

		<td>
		{$comparator['seller']}
		</td>

		<td>
		{$comparator['details']}
		</td>

		<td>
		{$comparator['price']}
		</td>

		<td>
		{$comparator['totalPrice']}
		</td>

		</tr>

	{/foreach}
	</table>

</div>

