<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * ExhibitPageBlock model.
 * 
 * @package ExhibitBuilder
 */
class ExhibitPageBlock extends Omeka_Record_AbstractRecord
{
    public $page_id;
    public $layout;
    public $options;
    public $text;
    public $order;

    protected $_related = array('ExhibitBlockAttachment' => 'getAttachments');

    protected function _delete()
    {
        if ($this->ExhibitBlockAttachment) {
            foreach ($this->ExhibitBlockAttachment as $attachment) {
                $attachment->delete();
            }
        }
    }
    
    public function getPage()
    {
        return $this->getTable('ExhibitPage')->find($this->page_id);
    }

    public function setData($data)
    {
        if (!empty($data['layout'])) {
            $this->layout = $data['layout'];
        }

        if (!empty($data['options'])) {
            $this->setOptions($data['options']);
        }

        if (!empty($data['text'])) {
            $this->text = $data['text'];
        } else {
            $this->text = null;
        }

        if (!empty($data['attachments'])) {
            $this->setAttachments($data['attachments']);
        } else {
            $this->setAttachments(array());
        }

        if (!empty($data['order'])) {
            $this->order = $data['order'];
        }
    }

    public function getOptions()
    {
        if (!empty($this->options)) {
            return json_decode($this->options, true);
        } else {
            return array();
        }
    }

    public function setOptions($options)
    {
        $this->options = json_encode($options);
    }

    public function getAttachments()
    {
        return $this->getTable('ExhibitBlockAttachment')->findByBlock($this);
    }
    
    public function setAttachments($attachmentsData, $deleteExtras = true)
    {
        // We have to have an ID to proceed.
        if (!$this->exists()) {
            $this->save();
        }

        $existingAttachments = $this->getAttachments();
        foreach ($attachmentsData as $i => $attachmentData) {
            if (!empty($existingAttachments)) {
                $attachment = array_pop($existingAttachments);
            } else {
                $attachment = new ExhibitBlockAttachment;
                $attachment->block_id = $this->id;
            }
            $attachment->setData($attachmentData);
            $attachment->save();
        }

        if ($deleteExtras) {
            foreach ($existingAttachments as $extraAttachment) {
                $extraAttachment->delete();
            }
        }
    }

    public function getLayout()
    {
        return ExhibitLayout::getLayout($this->layout);
    }

    public function getFormStem()
    {
        return 'blocks[' . ($this->order - 1) . ']';
    }
}
