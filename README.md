# CASH Music Platform #
At the heart of the CASH Music platform is a transactional framework tailored 
specifically to music promotion, sales, and digital distribution. We've abstracted 
things like data access, asset storage, and third party API management leaving 
both developers and end users with clean and intuitive workflows that mirror one
another. 

For end users we've built a standalone administration webapp that interacts with
the framework to allow no-code access to all of the functionality of the CASH
platform. A simple and secure login, a few web forms, and guided help on every 
page is all that stands between someone looking to bring new functionality to
their site. Even the embedding process is as little as copying a code in WordPress,
or cutting and pasting 2 simple lines of PHP. 

Developers follow a familiar Request/Response pattern, interacting with a local 
Request object that's loosely modeled after RESTful patterns. High level workflows
are defined as "Elements" in the framework, allowing access to complex trasactions
with a simple request. Each element contains a series of more granular requests
working off of concept abstractions called "Plants." Similar to a factory/worker
pattern, Plants spawn specific functionality by way of objects called "Seeds," 
which contain specific functionality to access third-party services, specific 
libraries, etc. 

All concepts in the framework are broken down into the categories of:

- System
- Elements
- Assets
- People
- Commerce
- Calendar

Those are the six plants present in the framework, and they represent the spectrum
of CASHRequest calls. Similarly, the end user is presented with a menu containing:
Elements, Assets, People, Commerce, and Calendar — with the ability to change some
system-wide settings, account details, etc. 

These structures mirror one another on purpose, allowing artists and end users to
speak the same language as developers. At the same time, there is amble flexibility
in the framework to allow for deep customization, easy third-party API definition, 
new and custom elements, etc. 
