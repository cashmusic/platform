Connect your Paypal account to accept payments via the CASH platform. To do this, you'll need a 
special API login, not your usual login credentials. Have no fear, it's easy to get.

### Step by step

 1. Log in to your paypal account and select "Profile" from the "My Account" menu up top.
 2. On your profile page select "My Selling Tools" from the left-hand menu. Then choose "update" for
    "API Access" from the list of options.
 3. You'll see two options on this page. Select "Option 2 - Request API credentials..." to the right.
 4. Now copy the API Username, API Password, and Signature from this page.
 5. In the CASH Admin open System Settings > Connections and add a new Paypal connection. Paste the 
    values from Paypal. For the field labeled "Sandbox" just enter "0" (zero. Yeah this should totally
    be a check-box. We'll get to it. Basically setting it to zero tells Paypal not to use a testing
    "sandbox" server...these are real-deal credentials.)

All done.

### Screenshots

Getting your API credentials:
![Log in and select 'Profile'](https://s3.amazonaws.com/cashmusic/permalink/help/paypal/1.jpg)
![Now 'My Selling Tools' and 'API Access'](https://s3.amazonaws.com/cashmusic/permalink/help/paypal/2.jpg)
![Request API Credentials](https://s3.amazonaws.com/cashmusic/permalink/help/paypal/3.jpg)
![Copy them](https://s3.amazonaws.com/cashmusic/permalink/help/paypal/4.jpg)