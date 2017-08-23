package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Comparator;
import java.util.Date;

/**
 * 
 * @author vhalommccarw
 *
 *  represents a person on the system
 *  
 */
public class MHVPatient extends BaseModel implements Serializable {

	/**
	 * 
	 */
	private static final long serialVersionUID = 6592673148166322388L;
	protected String userName;
	protected String lastName;
	protected String firstName;
	protected String middleName;

	protected String email;
	protected String ssn;
	protected String nssn;
	
	protected Date dob;
	protected String icn;
	
	protected String facility;
	
	protected MHVPatient(){
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
	public String getUserName() {
		return userName;
	}
	public void setUserName(String username) {
		this.userName = username;
	}
	public String getLastName() {
		return lastName;
	}
	public void setLastName(String lastName) {
		this.lastName = lastName;
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
	
	
	
	public static final Comparator<MHVPatient> USER_BY_NAME_SORTER = new Comparator<MHVPatient>() {
		public int compare(MHVPatient a, MHVPatient b) {
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
			MHVPatient x = (MHVPatient)a;
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
	
	public Date getDob() {
		return dob;
	}

	public void setDob(Date dob) {
		this.dob = dob;
	}

	public String getIcn() {
		return icn;
	}

	public void setIcn(String icn) {
		this.icn = icn;
	}

	public String getFacility() {
		return facility;
	}
	
	public void setFacility(String facility) {
		this.facility = facility;
	}

}
