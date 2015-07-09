package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Collection;

import gov.va.med.mhv.sm.enumeration.UserTypeEnum;

public class Administrator extends User implements Serializable{

	private static final long serialVersionUID = -4604433718982828729L;

	// national administrator?
	private boolean national;
	
	// list of facilities that this administrator can manipulate
	private Collection<Facility> facilities;
	
	// list of VISNs that this administrator can manipulate
	private Collection<Facility> visns;
	
	public Administrator(){
		super();
		this.userType = UserTypeEnum.ADMINISTRATOR;
		this.participantType = null;
	}


	public boolean isNational() {
		return national;
	}
	public void setNational(boolean national) {
		this.national = national;
	}
	public Collection<Facility> getFacilities() {
		return facilities;
	}
	public void setFacilities(Collection<Facility> facilities) {
		this.facilities = facilities;
	}
	public Collection<Facility> getVisns() {
		return visns;
	}
	public void setVisns(Collection<Facility> visns) {
		this.visns = visns;
	}
}
