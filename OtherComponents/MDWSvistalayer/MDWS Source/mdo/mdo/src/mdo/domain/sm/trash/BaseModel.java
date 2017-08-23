package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Date;

import gov.va.med.mhv.foundation.model.PersistentObject;


/**
 * 
 * @author vhalommccarw
 *
 * Establishes a base from which every table will have
 * an active, dateCreated, and dateModified field and
 * it will be updated automatically.
 *
 */

public abstract class BaseModel extends PersistentObject implements Serializable {
	
	private boolean active;
	private Date createdDate;
	private Date modifiedDate;
	
	public BaseModel(){
		this.active = true;
		createdDate = new Date();
		modifiedDate = new Date();
	}
	
	public boolean isActive() {
		return active;
	}
	
	public void setActive(boolean active) {
		this.active = active;
	}
	
	public Date getCreatedDate() {
		return createdDate;
	}
	
	public void setCreatedDate(Date dateCreated) {
		this.createdDate = dateCreated;
	}
	
	public Date getModifiedDate() {
		return modifiedDate;
	}
	
	public void setModifiedDate(Date dateModified) {
		this.modifiedDate = dateModified;
	}	
}
