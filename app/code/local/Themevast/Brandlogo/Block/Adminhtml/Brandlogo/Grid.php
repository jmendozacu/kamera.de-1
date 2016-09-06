<?php

class Themevast_Brandlogo_Block_Adminhtml_Brandlogo_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('brandlogoGrid');
      $this->setDefaultSort('brandlogo_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('brandlogo/brandlogo')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('brandlogo_id', array(
          'header'    => Mage::helper('brandlogo')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'brandlogo_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('brandlogo')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));
	  
      $this->addColumn('link', array(
          'header'    => Mage::helper('brandlogo')->__('Link'),
          'align'     =>'left',
          'index'     => 'link',
      ));

	  
      $this->addColumn('description', array(
			'header'    => Mage::helper('brandlogo')->__('Description'),
			'width'     => '500px',
			'index'     => 'description',
      ));
	  
      $this->addColumn('image',
        array(
          'header'=> Mage::helper('brandlogo')->__('Image'),
          'type' => 'image',
          'renderer'  => 'brandlogo/adminhtml_renderer_grid_column_images',
          'width' => 64,
          'index' => 'image',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('brandlogo')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('brandlogo')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('brandlogo')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('brandlogo')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('brandlogo')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('brandlogo_id');
        $this->getMassactionBlock()->setFormFieldName('brandlogo');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('brandlogo')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('brandlogo')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('brandlogo/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('brandlogo')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('brandlogo')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
