package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.sm.enumeration.ParticipantTypeEnum;

import java.io.Serializable;
import java.util.List;


public class DistributionGroup extends BaseModel implements Serializable, MailParticipant, Comparable<DistributionGroup> {

	
	/**
	 * 
	 */
	private static final long serialVersionUID = -6473761281127761688L;
	private String name;
	private Clinician owner;
	private List<User> members;
	private boolean publicGroup;
	private Long visnId;
	
	public ParticipantTypeEnum getParticipantType(){
		return ParticipantTypeEnum.DISTRIBUTION_GROUP;
	}


	public String getName() {
		return name;
	}
	public void setName(String name) {
		this.name = name;
	}
	public Clinician getOwner() {
		return owner;
	}
	public void setOwner(Clinician owner) {
		this.owner = owner;
	}
	public List<User> getMembers() {
		return members;
	}
	public void setMembers(List<User> members) {
		this.members = members;
	}

	public int compareTo(DistributionGroup other) {
		return this.getName().compareTo(other.getName());
	}


	public boolean isPublicGroup() {
		return publicGroup;
	}


	public void setPublicGroup(boolean publicGroup) {
		this.publicGroup = publicGroup;
	}


	public Long getVisnId() {
		return visnId;
	}


	public void setVisnId(Long visnId) {
		this.visnId = visnId;
	}

}

