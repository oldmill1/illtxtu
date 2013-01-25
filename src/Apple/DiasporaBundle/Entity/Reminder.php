<?php

namespace Apple\DiasporaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reminder
 */
class Reminder
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $task;

    /**
     * @var \DateTime
     */
    private $pulltime;

    /**
     * @var string
     */
    private $phonenumber;

    /**
     * @var string
     */
    private $donation;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set task
     *
     * @param string $task
     * @return Reminder
     */
    public function setTask($task)
    {
        $this->task = $task;
    
        return $this;
    }

    /**
     * Get task
     *
     * @return string 
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set pulltime
     *
     * @param \DateTime $pulltime
     * @return Reminder
     */
    public function setPulltime($pulltime)
    {
        $this->pulltime = $pulltime;
    
        return $this;
    }

    /**
     * Get pulltime
     *
     * @return \DateTime 
     */
    public function getPulltime()
    {
        return $this->pulltime;
    }

    /**
     * Set phonenumber
     *
     * @param string $phonenumber
     * @return Reminder
     */
    public function setPhonenumber($phonenumber)
    {
        $this->phonenumber = $phonenumber;
    
        return $this;
    }

    /**
     * Get phonenumber
     *
     * @return string 
     */
    public function getPhonenumber()
    {
        return $this->phonenumber;
    }

    /**
     * Set donation
     *
     * @param string $donation
     * @return Reminder
     */
    public function setDonation($donation)
    {
        $this->donation = $donation;
    
        return $this;
    }

    /**
     * Get donation
     *
     * @return string 
     */
    public function getDonation()
    {
        return $this->donation;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Reminder
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Reminder
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
}