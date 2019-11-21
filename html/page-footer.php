
<footer class="bg-primary text-white py-3">
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<div>
					<strong>Game Heretics</strong><br />
					<a href="mailto:<?= ADMINISTRATOR_EMAIL ?>"><?= ADMINISTRATOR_EMAIL ?></a><br />
					1355 Market St, Suite 900<br />
					San Francisco, CA 94103<br />
					<a href="tel:123-456-7890">(123) 456-7890</a><br />
				</div>
			</div>

			<div class="col-md-6">
				<div class="text-success text-right pr-3">

					<a class="display-4" href="#"><i class="fab fa-facebook-square"></i></a>
					<a class="display-4" href="#"><i class="fab fa-google-plus-square"></i></a>
					<a class="display-4" href="#"><i class="fab fa-twitter-square"></i></a>

				</div>
			</div>
		</div>
	</div>

</footer>

<script>
	// Disabling form submissions if there are invalid fields
	( function() {
		"use strict";
		window.addEventListener( "load", function() {
			// Fetch all the forms we want to apply custom Bootstrap validation styles to
			var forms = document.getElementsByClassName( "needs-validation" );
			// Loop over them and prevent submission
			var validation = Array.prototype.filter.call( forms, function( form ) {
				form.addEventListener( "submit", function( event ) {
					if( form.checkValidity() === false ) {
						event.preventDefault();
						event.stopPropagation();
					}
					form.classList.add( "was-validated" );
				}, false );
			} );
		}, false );
	} )();

	$( function() {
		$( "[data-toggle='tooltip']" ).tooltip( );
	} );
</script>

<?= FooterBuffer::get() ?>

</body>

</html>