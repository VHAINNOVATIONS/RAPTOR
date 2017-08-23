package gov.va.med.mhv.sm.model;

import gov.va.med.mhv.foundation.model.DomainObject;
import gov.va.med.mhv.foundation.util.Describeable;
import gov.va.med.mhv.foundation.util.DescriptionBuilder;

import java.io.Serializable;

import org.apache.commons.lang.StringUtils;

public class MhvIntegrationSettings implements DomainObject, Serializable,
	Describeable
{

	private static final long serialVersionUID = -3167644630286290431L;

	private String encryptionPassword = null;
	private String seed = null;
	private boolean expiration = false;
	private boolean productionMode = true;
	private String patientSource = null;
	private String administratorSource = null;
	private String clinicianSource = null;
	private int credentialsExpirationPeriod = 120;
	private String authenticationKey = null;

	public String getEncryptionPassword() {
		return encryptionPassword;
	}
	public void setEncryptionPassword(String password) {
		this.encryptionPassword = password;
	}
	public String getSeed() {
		return seed;
	}
	public void setSeed(String seed) {
		this.seed = seed;
	}
	public boolean getExpiration() {
		return expiration;
	}
	public void setExpiration(boolean expiration) {
		this.expiration = expiration;
	}
	public boolean isProductionMode() {
		return productionMode;
	}
	public void setProductionMode(boolean productionMode) {
		this.productionMode = productionMode;
	}

	public String getPatientSource() {
		return patientSource;
	}
	public void setPatientSource(String mhvSource) {
		this.patientSource = mhvSource;
	}
	public String getAdministratorSource() {
		return administratorSource;
	}
	public void setAdministratorSource(String adminSource) {
		this.administratorSource = adminSource;
	}
	public String getClinicianSource() {
		return clinicianSource;
	}
	public void setClinicianSource(String clinicianSource) {
		this.clinicianSource = clinicianSource;
	}
	public int getCredentialsExpirationPeriod() {
		return credentialsExpirationPeriod;
	}
	public void setCredentialsExpirationPeriod(int credentialsExpirationPeriod) {
		this.credentialsExpirationPeriod = credentialsExpirationPeriod;
	}
	public String getAuthenticationKey() {
		return authenticationKey;
	}
	public void setAuthenticationKey(String authenticationKey) {
		this.authenticationKey = authenticationKey;
	}

	public void describe(DescriptionBuilder builder) {
		if (builder == null) {
			return;
		}
		builder.header(this);
		// TODO - Refactor to newer version of DescriptionBuilder
		// using appendProperty etc. once newer version of foundation is used
		builder.append("[");
		builder.append("encryptionPassword=").append(StringUtils.repeat("*",
			(encryptionPassword != null) ? encryptionPassword.length() : 0));
		builder.append("; seed=").append(StringUtils.repeat("*",
			(seed != null) ? seed.length() : 0));
		builder.append("; expiration=").append(expiration);
		builder.append("; productionMode=").append(productionMode);
		builder.append("; patientSource=").append(patientSource);
		builder.append("; administratorSource=").append(administratorSource);
		builder.append("; clinicianSource=").append(clinicianSource);
		builder.append("; credentialsExpirationPeriod=").append(
			credentialsExpirationPeriod);
		builder.append("; authenticationKey=").append(authenticationKey);
		builder.append("]");
	}

	public static String describe(MhvIntegrationSettings settings) {
		return DescriptionBuilder.describe(settings);
	}
}
