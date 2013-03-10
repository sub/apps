<?php
/**
 * ownCloud - Contact object
 *
 * @author Thomas Tanghus
 * @copyright 2012 Thomas Tanghus (thomas@tanghus.net)
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Contacts;

/**
 * Subclass this class or implement IPIMObject interface for PIM objects
 */

class Contact extends VObject\VCard implements IPIMObject {

	/**
	 * The name of the object type in this case VCARD.
	 *
	 * This is used when serializing the object.
	 *
	 * @var string
	 */
	public $name = 'VCARD';

	protected $props = array();

	public function __construct($parent, $backend) {
		//\OCP\Util::writeLog('contacts', __METHOD__, \OCP\Util::DEBUG);
		$this->props['parent'] = $parent;
		$this->props['backend'] = $backend;
	}

	/**
	 * @return string|null
	 */
	public function getOwner() {
		return $this->props['owner'];
	}

	/**
	 * @return string|null
	 */
	public function getId() {
		return $this->props['id'];
	}

	/**
	 * If this object is part of a collection return a reference
	 * to the parent object, otherwise return null.
	 * @return IPIMObject|null
	 */
	function getParent() {
		return $this->props['parent'];
	}

	/** CRUDS permissions (Create, Read, Update, Delete, Share)
	 *
	 * @return integer
	 */
	function getPermissions() {
		return $this->props['permissions'];
	}

	/**
	 * @param integer $permission
	 * @return bool
	 */
	function hasPermission($permission) {
		return $this->getPermissions() & $permission;
	}

	/**
	 * Save the contact data to backend
	 *
	 * @return bool
	 */
	public function save() {
		if($this->id) {
			return $this->props['backend']->updateContact(
				$this->props['parent']->getID(), $this->props['id'], $this->serialize()
			);
		} else {
			$this->props['id'] = $this->props['backend']->createContact(
				$this->parent->getID(), $this->serialize()
			);
			if($this->props['id'] !== false) {
				$this->parent->setChildID();
			}
			return $this->props['id'] !== false;
		}
	}

	public function read($data = null) {
		// NOTE: Maybe this will mess with
		// the magic accessors.
		if(!$this->children) {
			if(!isset($this->props['carddata']) && is_null($data)) {
				$result = $this->props['backend']->getContact($this->parent->getID, $this->id);
				if($result) {
					if(isset($result['vcard']) && $result['vcard'] instanceof Contact) {
						$this->children = $result['vcard']->children;
						return true;
					} elseif(isset($result['carddata'])) {
						// Save internal values
						$data = $result['carddata'];
						$this->props['carddata'] = $result['carddata'];
						$this->props['lastmodified'] = $result['lastmodified'];
						$this->props['displayname'] = $result['displayname'];
						$this->props['permissions'] = $result['permissions'];
					} else {
						return false;
					}
				}
			}
			try {
				$obj = \Sabre\VObject\Reader::read(
					$data,
					\Sabre\VObject\Reader::OPTION_IGNORE_INVALID_LINES);
				$this->children = $obj->children;
			} catch (\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__ .
					'Error parsing carddata: ' . $e->getMessage(),
						\OCP\Util::ERROR);
				return false;
			}
		}
		return true;
	}

	public function lastModified() {
		if(!isset($this->props['lastmodified'])) {
			$this->read();
		}
		return isset($this->props['lastmodified'])
			? isset($this->props['lastmodified'])
			: null;
	}
}