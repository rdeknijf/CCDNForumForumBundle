<?php

/*
 * This file is part of the CCDN ForumBundle
 *
 * (c) CCDN (c) CodeConsortium <http://www.codeconsortium.com/> 
 * 
 * Available on github <http://www.github.com/codeconsortium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CCDNForum\ForumBundle\Controller;


use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * 
 * @author Reece Fowell <reece@codeconsortium.com> 
 * @version 1.0
 */
class SubscriptionController extends ContainerAware
{
	
	
	/**
	 *
	 * @access public
	 * @param $page
	 * @return RedirectResponse|RenderResponse
	 */
	public function showAction($page)
	{
		if ( ! $this->container->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException('You do not have permission to use this resource!');
		}
		
		$user = $this->container->get('security.context')->getToken()->getUser();
		
		$subscriptions = $this->container->get('ccdn_forum_forum.subscription.repository')->findForUserById($user->getId());

		// deal with pagination.
		$topics_per_page = $this->container->getParameter('ccdn_forum_forum.board.topics_per_page');
		$subscriptions->setMaxPerPage($topics_per_page);
		$subscriptions->setCurrentPage($page, false, true);
	
		// this is necessary for working out the last page for each topic.
		$posts_per_page = $this->container->getParameter('ccdn_forum_forum.topic.posts_per_page');
		
		$crumb_trail = $this->container->get('ccdn_component_crumb_trail.crumb_trail')
			->add($this->container->get('translator')->trans('crumbs.dashboard', array(), 'CCDNUserAdminBundle'), $this->container->get('router')->generate('cc_dashboard_index'), "sitemap")
			->add($this->container->get('translator')->trans('crumbs.forum_index', array(), 'CCDNForumForumBundle'), $this->container->get('router')->generate('cc_forum_category_index'), "home");
		
		return $this->container->get('templating')->renderResponse('CCDNForumForumBundle:Subscription:show.html.' . $this->getEngine(), array(
			'user_profile_route' => $this->container->getParameter('ccdn_forum_forum.user.profile_route'),
			'crumbs' => $crumb_trail,
			'pager' => $subscriptions,
			'posts_per_page' => $posts_per_page,
		));
        
	}
	
	public function subscribeAction($topic_id)
	{
		if ( ! $this->container->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException('You do not have permission to use this resource!');
		}
		
		$user = $this->container->get('security.context')->getToken()->getUser();
		
		$this->container->get('ccdn_forum_forum.subscription.manager')->subscribe($topic_id, $user)->flushNow();
		
		return new RedirectResponse($this->container->get('router')->generate('cc_forum_topic_show', array('topic_id' => $topic_id)) );
	}
	
	public function unsubscribeAction($topic_id)
	{
		if ( ! $this->container->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException('You do not have permission to use this resource!');
		}
		
		$user = $this->container->get('security.context')->getToken()->getUser();
		
		$this->container->get('ccdn_forum_forum.subscription.manager')->unsubscribe($topic_id, $user)->flushNow();
		
		return new RedirectResponse($this->container->get('router')->generate('cc_forum_topic_show', array('topic_id' => $topic_id)) );
	}
	
	/**
	 *
	 * @access protected
	 * @return string
	 */
	protected function getEngine()
    {
        return $this->container->getParameter('ccdn_forum_forum.template.engine');
    }

}