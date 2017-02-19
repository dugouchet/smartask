<?php

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;

class AppCache extends HttpCache
{
	protected function getOptions()
	{
		return array(
				'default_ttl' => 0,
				// ...
		);
	}
	
	//configure Symfony reverse proxy to support the PURGE HTTP method:
	protected function invalidate(Request $request, $catch = false)
	{
		if ('PURGE' !== $request->getMethod()) {
			return parent::invalidate($request, $catch);
		}
	
		if ('127.0.0.1' !== $request->getClientIp()) {
			return new Response(
					'Invalid HTTP method',
					Response::HTTP_BAD_REQUEST
					);
		}
	
		$response = new Response();
		if ($this->getStore()->purge($request->getUri())) {
			$response->setStatusCode(200, 'Purged');
		} else {
			$response->setStatusCode(404, 'Not found');
		}
	
		return $response;
	}
}
