<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
try {
    $installer->run("

        -- DROP TABLE IF EXISTS {$this->getTable('blog/blog')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('blog/blog')} (
            `post_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL DEFAULT '',
            `image` varchar(255) DEFAULT '',
            `post_content` text NOT NULL,
            `status` smallint(6) NOT NULL DEFAULT '0',
            `created_time` datetime DEFAULT NULL,
            `update_time` datetime DEFAULT NULL,
            `identifier` varchar(255) NOT NULL DEFAULT '',
            `user` varchar(255) NOT NULL DEFAULT '',
            `update_user` varchar(255) NOT NULL DEFAULT '',
            `meta_keywords` text NOT NULL,
            `meta_description` text NOT NULL,
            `comments` tinyint(11) NOT NULL,
            `tags` text NOT NULL,
            `short_content` text NOT NULL,
            PRIMARY KEY (`post_id`),
            UNIQUE KEY `identifier` (`identifier`)
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

        INSERT INTO {$this->getTable('blog/blog')} VALUES (NULL,'Dummy Title Text #01','themevast/blog/blog1.png','<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>',1,'2014-09-06 07:28:34','2014-10-01 04:07:48','blog1','Themes Vast','Themes Vast','Keywords','Description',0,'','<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form</p>'),('2','Dummy Title Text #02','themevast/blog/blog2.png','Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',1,'2014-10-01 00:28:18','2014-10-01 13:13:24','blog2','Themes Vast','Themes Vast','','',0,'','Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium'),('3','Dummy Title Text #03','themevast/blog/blog3.png','It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).',1,'2014-10-01 00:31:45','2014-10-16 06:24:10','blog3','Themes Vast','Themes Vast','','',0,'','It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.'),('4','Dummy Title Text #04','themevast/blog/blog4.png','<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.</p>',1,'2014-10-01 00:33:02','2014-10-03 09:26:15','blog4','Themes Vast','Themes Vast','','',0,'','<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC</p>');


        -- DROP TABLE IF EXISTS {$this->getTable('blog/cat')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('blog/cat')} (
            `cat_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL DEFAULT '',
            `identifier` varchar(255) NOT NULL DEFAULT '',
            `sort_order` tinyint(6) NOT NULL,
            `meta_keywords` text NOT NULL,
            `meta_description` text NOT NULL,
            PRIMARY KEY (`cat_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

        INSERT INTO {$this->getTable('blog/cat')} (`cat_id`, `title`, `identifier`) VALUES (NULL, 'News', 'news');


        -- DROP TABLE IF EXISTS {$this->getTable('blog/cat_store')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('blog/cat_store')} (
            `cat_id` smallint(6) unsigned DEFAULT NULL,
            `store_id` smallint(6) unsigned DEFAULT NULL
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;


        -- DROP TABLE IF EXISTS {$this->getTable('blog/comment')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('blog/comment')} (
            `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` smallint(11) NOT NULL DEFAULT '0',
            `comment` text NOT NULL,
            `status` smallint(6) NOT NULL DEFAULT '0',
            `created_time` datetime DEFAULT NULL,
            `user` varchar(255) NOT NULL DEFAULT '',
            `email` varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`comment_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

        INSERT INTO {$this->getTable('blog/comment')} (`comment_id` ,`post_id` ,`comment` ,`status` ,`created_time` ,`user` ,`email`)
        VALUES (NULL , '1', 'This is the first comment. It can be edited, deleted or set to unapproved so it is not displayed. This can be done in the admin panel.', '2', NOW( ) , 'Joe Blogs', 'joe@blogs.com');


        -- DROP TABLE IF EXISTS {$this->getTable('blog/post_cat')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('blog/post_cat')} (
            `cat_id` smallint(6) unsigned DEFAULT NULL,
            `post_id` smallint(6) unsigned DEFAULT NULL
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;


        -- DROP TABLE IF EXISTS {$this->getTable('blog/store')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('blog/store')} (
            `post_id` smallint(6) unsigned DEFAULT NULL,
            `store_id` smallint(6) unsigned DEFAULT NULL
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;


        -- DROP TABLE IF EXISTS {$this->getTable('blog/store')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('blog/store')} (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tag` varchar(255) NOT NULL,
            `tag_count` int(11) NOT NULL DEFAULT '0',
            `store_id` tinyint(4) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `tag` (`tag`,`tag_count`,`store_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

    ");
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();
