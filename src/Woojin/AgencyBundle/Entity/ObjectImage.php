<?php

namespace Woojin\AgencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ObjectImage
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ObjectImage
{
  /**
   * @ORM\ManyToOne(targetEntity="Object", inversedBy="object_images")
   * @var Object
   */
  protected $object;

  /**
   * @var integer
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   *
   * @var UploadedFile
   */
  protected $file;

  /**
   * @var string
   *
   * @ORM\Column(name="filename", type="string", length=255, nullable=true)
   */
  private $filename;


  protected function getUploadRootDir()
  {
    return $_SERVER[ 'DOCUMENT_ROOT' ].'/'.$this->getUploadDir();
  }

  protected function getUploadDir ()
  {
    return 'uploads/images';
  }

  public function getWebPath ()
  {
    if($this->filename === null){
      return null;
    }

    return $this->getUploadDir().'/'.$this->filename;
  }

  public function getAbsolutePath ()
  {
    if($this->filename === null){
      return null;
    }
    return $this->getUploadRootDir().'/'.$this->filename;
  }

  public function setFile (UploadedFile $file = null)
  {
    $this->file = $file;
  }

  /**
   *
   * @return UploadedFile
   */
  public function getFile ()
  {
    return $this->file;
  }

  public function upload ()
  {
    if ($this->file === null) {
      return;
    }

    if ( !file_exists( $this->getUploadRootDir() ) ) {
      mkdir($this->getUploadRootDir(), 0777, true);
    }

    $this->filename = "{$this->getId()}.{$this->getFile()->guessExtension()}";

    $this->getFile()->move( $this->getUploadRootDir(), $this->filename );

    $this->setFile(null);
  }

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
   * Set filename
   *
   * @param string $filename
   * @return ObjectImage
   */
  public function setFilename($filename)
  {
      $this->filename = $filename;
  
      return $this;
  }

  /**
   * Get filename
   *
   * @return string 
   */
  public function getFilename()
  {
      return $this->filename;
  }

  /**
   * Set object
   *
   * @param \Woojin\AgencyBundle\Entity\Object $object
   * @return ObjectImage
   */
  public function setObject(\Woojin\AgencyBundle\Entity\Object $object = null)
  {
      $this->object = $object;
  
      return $this;
  }

  /**
   * Get object
   *
   * @return \Woojin\AgencyBundle\Entity\Object 
   */
  public function getObject()
  {
      return $this->object;
  }
}