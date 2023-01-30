### URL Rewrite Import Using CSV
- Only CSV file types are allowed
- Module performs only permanent redirects
- Maximum 2MBs File size is allowed
- CSV file should contain only two columns
- First column should contain request URL and 2nd column should contain target URL
- Please do not include base URLs of the store in request or target URLs
- Please do not add request URLs that contain request parameters like BASE_URL?search=abc&xyz, if request URL contains such URLs, this module trims that part and generates the rewrite with the remaining URL.

#### How to Upload CSV?
- Install the Module
- Go to Admin > Marketing > SEO & Search > Import URL Rewrites
- Select The store for which you want to import URL rewrites and click import CSV
- On Success, you will see a success message.
- On Error, A File is downloaded with the records that were not uploaded.
