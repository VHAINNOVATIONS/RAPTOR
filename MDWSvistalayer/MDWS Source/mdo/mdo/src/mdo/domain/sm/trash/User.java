package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Collection;
import java.util.Comparator;
import java.util.Date;
import java.util.List;

import gov.va.med.mhv.sm.enumeration.EmailNotificationEnum;
import gov.va.med.mhv.sm.enumeration.MessageFilterEnum;
import gov.va.med.mhv.sm.enumeration.ParticipantTypeEnum;
import gov.va.med.mhv.sm.enumeration.UserStatusEnum;
import gov.va.med.mhv.sm.enumeration.UserTypeEnum;

/**
 * 
 * @author vhalommccarw
 *
 *  represents a person on the system
 *  
 */
public class User extends BaseModel implements Serializable, MailParticipant{

	/**
	 * 
	 */
	private static final long serialVersionUID = 3758535016199858278L;
	protected ParticipantTypeEnum participantType;
	protected UserTypeEnum userType;
	protected String username;
	protected String lastName;
	protected String firstName;
	protected String middleName;
	protected Collection<TriageGroup> userAssociatedGroups;

	protected String email;
	//list of groups that the actor belongs 
	protected List<TriageGroup> groups;
	protected UserStatusEnum status;
	protected EmailNotificationEnum emailNotification;
	protected MessageFilterEnum messageFilter;
	protected Date lastNotification;
	protected String ssn;
	protected String nssn;
	
	protected Mailbox mailbox;
	
	protected List<AdminRole> adminRoles;
	
	
	protected User(){
		// set some acceptable defaults
		emailNotification = EmailNotificationEnum.NONE;
		status = UserStatusEnum.OPT_OUT;
		messageFilter = MessageFilterEnum.ALL;
	}
	
	public String getEmail() {
		return email;
	}
	public void setEmail(String email) {
		this.email = email;
	}
	public String getFirstName() {
		return firstName;
	}
	public void setFirstName(String firstName) {
		this.firstName = firstName;
	}
	public List<TriageGroup> getGroups() {
		return groups;
	}
	public void setGroups(List<TriageGroup> groups) {
		this.groups = groups;
	}
	public String getUsername() {
		return username;
	}
	public void setUsername(String username) {
		this.username = username;
	}
	public String getLastName() {
		return lastName;
	}
	public void setLastName(String lastName) {
		this.lastName = lastName;
	}
	

	
	public Mailbox getMailbox() {
		return mailbox;
	}
	public void setMailbox(Mailbox mailbox) {
		this.mailbox = mailbox;
	}
	public UserTypeEnum getUserType(){
		return userType;
	}
	public List<AdminRole> getAdminRoles() {
		return adminRoles;
	}
	public void setAdminRoles(List<AdminRole> adminRoles) {
		this.adminRoles = adminRoles;
	}
	public EmailNotificationEnum getEmailNotification() {
		return emailNotification;
	}
	public void setEmailNotification(EmailNotificationEnum emailNotification) {
		this.emailNotification = emailNotification;
	}
	public MessageFilterEnum getMessageFilter() {
		return messageFilter;
	}
	public void setMessageFilter(MessageFilterEnum messageFilter) {
		this.messageFilter = messageFilter;
	}
	public Date getLastNotification() {
		return lastNotification;
	}
	public void setLastNotification(Date lastNotification) {
		this.lastNotification = lastNotification;
	}
	public UserStatusEnum getStatus() {
		return status;
	}
	public void setStatus(UserStatusEnum status) {
		this.status = status;
	}
	public ParticipantTypeEnum getParticipantType(){
		return participantType;
	}
	public String getName(){
		return lastName + ", " + firstName;
	}
	public String getSsn() {
		return ssn;
	}
	public void setSsn(String ssn) {
		this.ssn = ssn;
	}
	public String getNssn() {
		return nssn;
	}
	public void setNssn(String nssn) {
		this.nssn = nssn;
	}
	
	public static final Comparator<User> USER_BY_NAME_SORTER = new Comparator<User>() {
		public int compare(User a, User b) {
			if(a == null || b == null) return 0;
			int result = (a.getLastName().toUpperCase()).compareTo(b.getLastName().toUpperCase());
			if ( result == 0 ) {
				result = a.getFirstName().toUpperCase().compareTo(b.getFirstName().toUpperCase());
			}
			return result;
		}
	};
	
	public boolean equals(Object a){
		try{
			User x = (User)a;
			if(this.getId().equals(x.getId())) 
				return true;
			return false;
		}catch(Exception e){
			return false;
		}
	}

	public int hashCode(){
		return this.getId().hashCode();
	}
	
	public String toString(){
		Long id = this.getId();
		String name = this.lastName;
		if(name == null) name = "?????";
		
		if(id == null) {
			return "[unsaved]" + "^" + name;
		}
		return id + "^" + this.lastName;
	}

 	public String getMiddleName() {
		return middleName;
	}

	public void setMiddleName(String middleName) {
		this.middleName = middleName;
	}

	public Collection<TriageGroup> getUserAssociatedGroups() {
		return userAssociatedGroups;
	}

	public void setUserAssociatedGroups(Collection<TriageGroup> userAssociatedGroups) {
		this.userAssociatedGroups = userAssociatedGroups;
	}

}
