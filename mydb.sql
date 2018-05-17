/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : mydb

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2018-05-17 01:20:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `article`
-- ----------------------------
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `content` text,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`article_id`,`user_id`),
  KEY `fk_article_user_idx` (`user_id`),
  CONSTRAINT `fk_article_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of article
-- ----------------------------
INSERT INTO `article` VALUES ('1', 'aaaa', 'bbbbb', '1', '0000-00-00 00:00:00');
INSERT INTO `article` VALUES ('2', 'aaaa', 'bbbbb', '1', '0000-00-00 00:00:00');
INSERT INTO `article` VALUES ('3', 'hi', 'hello,world!!!!!', '1', '2018-05-14 13:29:24');
INSERT INTO `article` VALUES ('4', '你好', '世界', '2', '2018-05-14 13:47:50');

-- ----------------------------
-- Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` char(32) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `username` (`username`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', 'admin', '292e6842fb3b64032724e70f24b920f5', '2018-05-14 12:23:19');
INSERT INTO `user` VALUES ('2', 'damon', '1e9c84f0efe88028bae15532c01d277d', '2018-05-18 20:14:22');
INSERT INTO `user` VALUES ('3', 'connie', '1e9c84f0efe88028bae15532c01d277d', '2018-05-14 12:13:50');
INSERT INTO `user` VALUES ('4', 'damon11', 'b60a693cd442d2e307359516db554532', '2018-05-15 18:31:15');
INSERT INTO `user` VALUES ('5', 'admin3', '292e6842fb3b64032724e70f24b920f5', '2018-05-16 12:37:09');
