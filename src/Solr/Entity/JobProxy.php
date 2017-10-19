<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */
namespace Solr\Entity;

use Core\Entity\AttachableEntityInterface;
use Core\Entity\AttachableEntityManager;
use Core\Entity\EntityInterface;
use Core\Entity\IdentifiableEntityInterface;
use CoreTestUtils\TestCase\TestInheritanceTrait;
use CoreTestUtils\TestCase\TestSetterGetterTrait;
use CoreTestUtils\TestCase\TestUsesTraitsTrait;
use Jobs\Entity\JobInterface;
use Core\Entity\AbstractIdentifiableModificationDateAwareEntity;
use ArrayAccess;
use Jobs\Entity\TemplateValues;
use Solr\Bridge\Util;

/**
 * This class decorates an instance of JobInterface and allows returning data
 * from Solr result instead of data from the decorated one.
 *
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.27
 * @package Solr\Entity
 */
class JobProxy extends AbstractIdentifiableModificationDateAwareEntity implements JobInterface
{

    use TestInheritanceTrait, TestUsesTraitsTrait, TestSetterGetterTrait;



    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var ArrayAccess
     */
    protected $solrResult;

    /**
     * @param JobInterface $job
     * @param ArrayAccess $solrResult
     */
    public function __construct(JobInterface $job, ArrayAccess $solrResult)
    {
        $this->job = $job;
        $this->solrResult = $solrResult;
    }

    /**
     * @see \Jobs\Entity\JobInterface::getId()
     */
    public function getId()
    {
        return $this->getSolrValue('id') ?: $this->job->getId();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getApplications()
     */
    public function getApplications()
    {
        return $this->job->getApplications();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getApplyId()
     */
    public function getApplyId()
    {
        return $this->getSolrValue('applyId') ?: $this->job->getApplyId();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getAtsEnabled()
     */
    public function getAtsEnabled()
    {
        return $this->job->getAtsEnabled();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getAtsMode()
     */
    public function getAtsMode()
    {
        return $this->job->getAtsMode();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getCompany()
     */
    public function getCompany()
    {
        return $this->getSolrValue('organizationName') ?:$this->job->getCompany();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getContactEmail()
     */
    public function getContactEmail()
    {
        return $this->getSolrValue('applicationEmail') ?: $this->job->getContactEmail();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getDatePublishEnd()
     */
    public function getDatePublishEnd()
    {
        $date = $this->getSolrValue('datePublishEnd');
        
        return $date ? Util::convertSolrDateToPhpDateTime($date) : $this->job->getDatePublishEnd();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getDatePublishStart()
     */
    public function getDatePublishStart()
    {
        $date = $this->getSolrValue('datePublishStart');
        
        return $date ? Util::convertSolrDateToPhpDateTime($date) : $this->job->getDatePublishStart();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getHistory()
     */
    public function getHistory()
    {
        return $this->job->getHistory();
        
    }

    /**
     * @see \Jobs\Entity\JobInterface::getLanguage()
     */
    public function getLanguage()
    {
        return $this->getSolrValue('lang') ?: $this->job->getLanguage();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getLink()
     */
    public function getLink()
    {
        $link = $this->getSolrValue('link');
        
        if ($link) {
            // sanitize invalid Solr schema
            return is_array($link) ? reset($link) : $link;
        }
        
        return $this->job->getLink();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getLocation()
     */
    public function getLocation()
    {
        // check for a locations subquery result
        if (isset($this->solrResult['locations'])
            && $this->solrResult['locations'] instanceof ArrayAccess
            && isset($this->solrResult['locations']['docs'])
        )
        {
            // get concatenated list of cities from the locations
            $locations = trim(implode(', ', array_unique(array_map(function(ArrayAccess $doc)
            {
                return isset($doc->city) ? trim($doc->city) : '';
            }, $this->solrResult['locations']['docs']))));
            
            if ($locations) {
                return $locations;
            }
        }
        
        return $this->getSolrValue('location') ?: $this->job->getLocation();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getLocations()
     */
    public function getLocations()
    {
        return $this->job->getLocations();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getOrganization()
     */
    public function getOrganization()
    {
        return $this->job->getOrganization();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getPortals()
     */
    public function getPortals()
    {
        return $this->job->getPortals();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getReference()
     */
    public function getReference()
    {
        return $this->job->getReference();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getStatus()
     */
    public function getStatus()
    {
        return $this->job->getStatus();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getTermsAccepted()
     */
    public function getTermsAccepted()
    {
        return $this->job->getTermsAccepted();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getTitle()
     */
    public function getTitle()
    {
        return $this->getSolrValue('title') ?: $this->job->getTitle();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getUriApply()
     */
    public function getUriApply()
    {
        return $this->job->getUriApply();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getUriPublisher()
     */
    public function getUriPublisher()
    {
        return $this->job->getUriPublisher();
    }

    /**
     * @see \Jobs\Entity\JobInterface::getUser()
     */
    public function getUser()
    {
        return $this->job->getUser();
    }

    /**
     * @see \Jobs\Entity\JobInterface::setApplications()
     */
    public function setApplications(\Doctrine\Common\Collections\Collection $applications)
    {
        return $this->job->setApplications($applications);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setApplyId()
     */
    public function setApplyId($applyId)
    {
        return $this->job->setApplyId($applyId);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setAtsEnabled()
     */
    public function setAtsEnabled($atsEnabled)
    {
        return $this->job->setAtsEnabled($atsEnabled);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setAtsMode()
     */
    public function setAtsMode(\Jobs\Entity\AtsMode $mode)
    {
        return $this->job->setAtsMode($mode);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setCompany()
     */
    public function setCompany($company)
    {
        return $this->job->setCompany($company);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setContactEmail()
     */
    public function setContactEmail($email)
    {
        return $this->job->setContactEmail($email);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setDatePublishEnd()
     */
    public function setDatePublishEnd($datePublishEnd)
    {
        return $this->job->setDatePublishEnd($datePublishEnd);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setDatePublishStart()
     */
    public function setDatePublishStart($datePublishStart)
    {
        return $this->job->setDatePublishStart($datePublishStart);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setHistory()
     */
    public function setHistory(\Doctrine\Common\Collections\Collection $history)
    {
        return $this->job->setHistory($history);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setLanguage()
     */
    public function setLanguage($language)
    {
        return $this->job->setLanguage($language);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setLink()
     */
    public function setLink($link)
    {
        return $this->job->setLink($link);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setLocation()
     */
    public function setLocation($location)
    {
        return $this->job->setLocation($location);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setLocations()
     */
    public function setLocations($locations)
    {
        return $this->job->setLocations($locations);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setOrganization()
     */
    public function setOrganization(\Organizations\Entity\OrganizationInterface $organization = null)
    {
        return $this->job->setOrganization($organization);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setPortals()
     */
    public function setPortals(array $portals)
    {
        return $this->job->setPortals($portals);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setReference()
     */
    public function setReference($reference)
    {
        return $this->job->setReference($reference);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setStatus()
     */
    public function setStatus($status)
    {
        return $this->job->setStatus($status);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setTermsAccepted()
     */
    public function setTermsAccepted($flag)
    {
        return $this->job->setTermsAccepted($flag);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setTitle()
     */
    public function setTitle($title)
    {
        return $this->job->setTitle($title);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setUriApply()
     */
    public function setUriApply($uriApply)
    {
        return $this->job->setUriApply($uriApply);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setUriPublisher()
     */
    public function setUriPublisher($uriPublisher)
    {
        return $this->job->setUriPublisher($uriPublisher);
    }

    /**
     * @see \Jobs\Entity\JobInterface::setUser()
     */
    public function setUser(\Auth\Entity\UserInterface $user)
    {
        return $this->job->setUser($user);
    }
    
    /**
     * @see \Zend\Permissions\Acl\Resource\ResourceInterface::getResourceId()
     */
    public function getResourceId()
    {
        return $this->job->getResourceId();
    }

    /**
     * @see \Core\Entity\PermissionsAwareInterface::getPermissions()
     */
    public function getPermissions()
    {
        return $this->job->getPermissions();
    }

    /**
     * @see \Core\Entity\PermissionsAwareInterface::setPermissions()
     */
    public function setPermissions(\Core\Entity\PermissionsInterface $permissions)
    {
        return $this->job->setPermissions($permissions);
    }
    
    /**
     * @param string $key
     * @return mixed
     */
    public function getSolrValue($key)
    {
        return isset($this->solrResult[$key]) ? $this->solrResult[$key] : null;
    }

    /**
     * Get meta data.
     *
     * Returns the whole meta data array, if no <i>$key</i> is provided.
     * Returns <i>null</i>, if there is no meta data for the provided <i>$key</i>.
     *
     * @param null|string $key
     *
     * @return array|mixed|null
     */
    public function getMetaData($key = null)
    {
        return $this->job->getMetaData($key);
    }

    /**
     * Set meta data.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function setMetaData($key, $value)
    {
        $this->job->setMetaData($key, $value);
        return $this;
    }

    /**
     * Check if a meta data with a specific key is available.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasMetaData($key)
    {
        return $this->job->hasMetaData($key);
    }

    /**
     * Gets the Values of a job template
     *
     * @return TemplateValues
     */
    public function getTemplateValues()
    {
        return $this->job->getTemplateValues();
    }

    /**
     * @param EntityInterface $templateValues
     *
     * @return $this
     */
    public function setTemplateValues(EntityInterface $templateValues = null)
    {
        $this->job->setTemplateValues($templateValues);
        return $this;
    }

    /**
     * @return \Jobs\Entity\Classifications
     */
    public function getClassifications()
    {
        return $this->job->getClassifications();
    }

    /**
     * @param \Jobs\Entity\Classifications $classifications
     *
     * @return self
     */
    public function setClassifications($classifications)
    {
        $this->job->setClassifications($classifications);
        return $this;
    }

    /**
     * @param AttachableEntityManager $attachableEntityManager
     *
     * @throws \LogicException If attachable entity manager is already set
     */
    public function setAttachableEntityManager(AttachableEntityManager $attachableEntityManager)
    {
        $this->job->setAttachableEntityManager($attachableEntityManager);
        return $this;
    }

    /**
     * Adds an $entity using an optional $key.
     * If $key is not provided then $entity's FQCN will be used as a key
     * Any existing $entity with the same $key will be replaced.
     *
     * @param IdentifiableEntityInterface $entity
     * @param string                      $key
     *
     * @return AttachableEntityInterface
     */
    public function addAttachedEntity(IdentifiableEntityInterface $entity, $key = null)
    {
        $this->job->addAttachedEntity($entity, $key);
        return $this;
    }

    /**
     * @param string $key
     *
     * @return IdentifiableEntityInterface|null
     */
    public function getAttachedEntity($key)
    {
        $this->job->getAttachedEntity($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function removeAttachedEntity($key)
    {
        return $this->job->removeAttachedEntity($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasAttachedEntity($key)
    {
        $this->job->hasAttachedEntity($key);
    }

    /**
     * Creates an entity and adds it.
     *
     * @param string       $entityClass
     * @param array|string $values
     * @param null|string  $key
     *
     * @return \Core\Entity\EntityInterface
     * @since 0.29
     */
    public function createAttachedEntity($entityClass, $values = [], $key = null)
    {
        $this->job->createAttachedEntity($entityClass, $values, $key);
        return $this;
    }


}