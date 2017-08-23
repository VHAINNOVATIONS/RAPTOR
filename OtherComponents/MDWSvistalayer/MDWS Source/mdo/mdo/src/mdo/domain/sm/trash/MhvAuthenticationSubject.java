package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.foundation.util.Describeable;
import gov.va.med.mhv.foundation.util.DescriptionBuilder;
import gov.va.med.mhv.sm.util.UserUtils;

import java.io.Serializable;
import java.util.Date;

public class MhvAuthenticationSubject implements Serializable, Describeable {

	private static final long serialVersionUID = -3473207683272248458L;
	
	public static String describe(MhvAuthenticationSubject settings) {
		return DescriptionBuilder.describe(settings);
	}
	
	private String userName = null;
	private String firstName = null;
	private String lastName = null;
	private String email = null;
	private String icn = null;
	private String ssn = null;
	private Date dob = null;
	private String source = null;
	private Boolean authenticated = null;
	private Boolean national = null;
	private String checksum = null;
	private Long timestamp = null;
	private String[] facilities = null;
	private String[] visns = null;
	private boolean requiresCredentials =  false; 

	public String getUserName() {
		return userName;
	}
	public void setUserName(String userName) {
		this.userName = userName;
	}

	public String getFirstName() {
		return firstName;
	}
	public void setFirstName(String firstName) {
		this.firstName = firstName;
	}

	public String getLastName() {
		return lastName;
	}
	public void setLastName(String lastName) {
		this.lastName = lastName;
	}

	public String getEmail() {
		return email;
	}
	public void setEmail(String email) {
		this.email = email;
	}

	public String getIcn() {
		return icn;
	}
	public void setIcn(String icn) {
		this.icn = icn;
	}

	public String getSsn() {
		return ssn;
	}
	public void setSsn(String ssn) {
		this.ssn = ssn;
	}

	public Date getDob() {
		return dob;
	}
	public void setDob(Date dob) {
		this.dob = dob;
	}

	public String getSource() {
		return source;
	}
	public void setSource(String source) {
		this.source = source;
	}

	public Boolean getAuthenticated() {
		return authenticated;
	}
	public void setAuthenticated(Boolean authenticated) {
		this.authenticated = authenticated;
	}

	public Boolean getNational() {
		return national;
	}
	public void setNational(Boolean national) {
		this.national = national;
	}
	public String getChecksum() {
		return checksum;
	}
	public void setChecksum(String checksum) {
		this.checksum = checksum;
	}

	public Long getTimestamp() {
		return timestamp;
	}
	public void setTimestamp(Long timestamp) {
		this.timestamp = timestamp;
	}

	public String[] getFacilities() {
		return facilities;
	}
	public void setFacilities(String[] facilities) {
		this.facilities = facilities;
	}

	public String[] getVisns() {
		return visns;
	}
	public void setVisns(String[] visns) {
		this.visns = visns;
	}

	public boolean getRequiresCredentials() {
		return requiresCredentials;
	}
	public void setRequiresCredentials(boolean generateCredentials) {
		this.requiresCredentials = generateCredentials;
	}
	
	public void describe(DescriptionBuilder builder) {
		if (builder == null) {
			return;
		}
		builder.header(this);
		// TODO - Refactor to newer version of DescriptionBuilder
		// using appendProperty etc. once newer version of foundation is used
		builder.append("[");
		builder.append("userName=").append(getUserName());
		builder.append("firstName=").append(getFirstName());
		builder.append("lastName=").append(getLastName());
		builder.append("email=").append(getEmail());
		builder.append("ssn=").append(UserUtils.maskSsn(getSsn()));
		builder.append("icn=").append(UserUtils.maskIcn(getIcn()));
		builder.append("dob=").append(getDob());
		builder.append("authenticated=").append(getAuthenticated());
		builder.append("national=").append(getNational());
		builder.append("timestamp=").append(getTimestamp());
		builder.append("checksum=").append(getChecksum());
		builder.append("source=").append(getSource());
		builder.append("stations=").append(getFacilities());
		builder.append("visns=").append(getVisns());
		builder.append("]");
	}

}
