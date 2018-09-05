(function () {
	$(document).ready(function () {
		// Custom styling can be passed to options when creating an Element.
		const style = {
			base: {
		    // Add your base input styles here. For example:
		    fontSize: '16px',
		    lineHeight: '24px',
			},
		};

		// Create an instance of the card Element
		const card = elements.create('card', {style});

		// Add an instance of the card Element into the `card-element` <div>
		card.mount('#card-element');

		card.addEventListener('change', ({error}) => {
		  const displayError = document.getElementById('card-errors');
		  if (error) {
		    displayError.textContent = error.message;
		  } else {
		    displayError.textContent = '';
		  }
		});

		// Create a token or display an error when the form is submitted.
		var form = document.getElementById('formMembershipSignup');
		form.addEventListener('submit', function(event) {
		  event.preventDefault();

		  stripe.createToken(card).then(function(result) {
		    if (result.error) {
		      // Inform the user if there was an error
		      var errorElement = document.getElementById('card-errors');
		      errorElement.textContent = result.error.message;
		    } else {
		      // Send the token to your server
		      stripeTokenHandler(result.token);
		    }
		  });
		});
	})

	function stripeTokenHandler(token) {
	  // Insert the token ID into the form so it gets submitted to the server
	  var form = document.getElementById('formMembershipSignup');
	  var hiddenInput = document.createElement('input');
	  hiddenInput.setAttribute('type', 'hidden');
	  hiddenInput.setAttribute('name', 'stripeToken');
	  hiddenInput.setAttribute('value', token.id);
	  form.appendChild(hiddenInput);

	  // Submit the form
	  form.submit();
	}
})()