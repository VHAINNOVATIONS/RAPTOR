using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.dao.vista.fhie;

namespace gov.va.medora.mdo.dao
{
    public abstract class AbstractDaoFactory
    {
        public const int PVISTA = 0; // pooled vista
        public const int VISTA = 1;
        public const int FHIE = 2;
        public const int HL7 = 3;
        public const int RPMS = 4;
        public const int NPT = 100;
        public const int VBACORP = 200;
        public const int ADR = 201;
        public const int MHV = 202;
        public const int VADIR = 203;
        public const int CDW = 204;
        public const int SM = 205;
        public const int RDW = 206;
        public const int LDAP = 300;

        public const int XVISTA = 998;
        public const int MOCK = 999;
        

        public abstract AbstractConnection getConnection(DataSource dataSource);
        public abstract IUserDao getUserDao(AbstractConnection cxn);
        public abstract IPatientDao getPatientDao(AbstractConnection cxn);
        public abstract IClinicalDao getClinicalDao(AbstractConnection cxn);
        public abstract IEncounterDao getEncounterDao(AbstractConnection cxn);
        public abstract IPharmacyDao getPharmacyDao(AbstractConnection cxn);
        public abstract ILabsDao getLabsDao(AbstractConnection cxn);
        public abstract IToolsDao getToolsDao(AbstractConnection cxn);
        public abstract INoteDao getNoteDao(AbstractConnection cxn);
        public abstract IVitalsDao getVitalsDao(AbstractConnection cxn);
        public abstract IChemHemDao getChemHemDao(AbstractConnection cxn);
        public abstract IClaimsDao getClaimsDao(AbstractConnection cxn);
        public abstract IConsultDao getConsultDao(AbstractConnection cxn);
        public abstract IRemindersDao getRemindersDao(AbstractConnection cxn);
        public abstract ILocationDao getLocationDao(AbstractConnection cxn);
        public abstract IOrdersDao getOrdersDao(AbstractConnection cxn);
        public abstract IRadiologyDao getRadiologyDao(AbstractConnection cxn);
        public abstract ISchedulingDao getSchedulingDao(AbstractConnection cxn);
        public abstract IProblemDao getProblemDao(AbstractConnection cxn);

        public Object getDaoByName(string daoName, AbstractConnection cxn)
        {
    	    if (daoName == "AbstractConnection" || daoName.EndsWith("Connection"))
    	    {
    		    return getConnection(cxn.DataSource);
    	    }
            if (daoName == "ISchedulingDao")
            {
                return getSchedulingDao(cxn);
            }
            if (daoName == "IToolsDao")
            {
                return getToolsDao(cxn);
            }
            if (daoName == "IPatientDao")
            {
                return getPatientDao(cxn);
            }
            if (daoName == "IUserDao")
            {
                return getUserDao(cxn);
            }
            if (daoName == "IClinicalDao")
            {
                return getClinicalDao(cxn);
            }
            if (daoName == "IEncounterDao")
            {
                return getEncounterDao(cxn);
            }
            if (daoName == "IPharmacyDao")
            {
                return getPharmacyDao(cxn);
            }
            if (daoName == "ILabsDao")
            {
                return getLabsDao(cxn);
            }
            if (daoName == "INoteDao")
            {
                return getNoteDao(cxn);
            }
            if (daoName == "IVitalsDao")
            {
                return getVitalsDao(cxn);
            }
            if (daoName == "IChemHemDao")
            {
                return getChemHemDao(cxn);
            }
            if (daoName == "IClaimsDao")
            {
                return getClaimsDao(cxn);
            }
            if (daoName == "IConsultDao")
            {
                return getConsultDao(cxn);
            }
            if (daoName == "IRemindersDao")
            {
                return getRemindersDao(cxn);
            }
            if (daoName == "ILocationDao")
            {
                return getLocationDao(cxn);
            }
            if (daoName == "IOrdersDao")
            {
                return getOrdersDao(cxn);
            }
            if (daoName == "IRadiologyDao")
            {
                return getRadiologyDao(cxn);
            }
            if (daoName == "IProblemDao")
            {
                return getProblemDao(cxn);
            }
            return null;
        }

        public static int getConstant (string value)
        {
            if (value == "PVISTA")
            {
                return PVISTA;
            }
            if (value == "VISTA")
            {
                return VISTA;
            }
            if (value == "FHIE") 
            {
        	    return FHIE;
            }
            if (value == "HL7") 
            {
        	    return HL7;
            }
            if (value == "RPMS")
            {
                return RPMS;
            }
            if (value == "NPT")
            {
                return NPT;
            }
            if (value == "VBACORP")
            {
                return VBACORP;
            }
            if (value == "ADR")
            {
                return ADR;
            }
            if (value == "MHV")
            {
                return MHV;
            }
            if (value == "VADIR")
            {
                return VADIR;
            }
            if (String.Equals("CDW", value, StringComparison.CurrentCultureIgnoreCase))
            {
                return CDW;
            }
            if (value == "XVISTA")
            {
                return XVISTA;
            }
            if (value == "MOCK")
            {
                return MOCK;
            }
            if (String.Equals(value, "SM", StringComparison.CurrentCultureIgnoreCase))
            {
                return SM;
            }
            if (String.Equals(value, "LDAP", StringComparison.CurrentCultureIgnoreCase))
            {
                return LDAP;
            }
            if (String.Equals(value, "RDW", StringComparison.CurrentCultureIgnoreCase))
            {
                return RDW;
            }
            return 0;
        }

        public static AbstractDaoFactory getDaoFactory (int protocol)
        {
            switch (protocol)
		    {
                case PVISTA:
                    return new VistaConnectionPoolDaoFactory();
                case VISTA:
                    return new VistaDaoFactory();
                case FHIE:
                    return new FhieDaoFactory();
                case HL7:
                    return new gov.va.medora.mdo.dao.hl7.HL7DaoFactory();
                case NPT:
                    return new gov.va.medora.mdo.dao.sql.npt.NptDaoFactory();
                case VBACORP:
                    return new gov.va.medora.mdo.dao.oracle.vbacorp.VbacorpDaoFactory();
                case ADR:
                    return new gov.va.medora.mdo.dao.oracle.adr.AdrDaoFactory();
                //case MHV:
                //    return new gov.va.medora.mdo.dao.oracle.mhv.MhvDaoFactory();
                case VADIR:
                    return new gov.va.medora.mdo.dao.oracle.vadir.VadirDaoFactory();
                case CDW:
                    return new gov.va.medora.mdo.dao.sql.cdw.CdwDaoFactory();
                case MOCK:
                    return new MockDaoFactory();
                case XVISTA:
                    return new XDaoFactory();
                case SM:
                    return new dao.oracle.mhv.MhvDaoFactory();
                case LDAP:
                    return new dao.ldap.LdapDaoFactory();
                //case RDW:
                //    return new dao.soap.rdw.RdwDaoFactory();
                default:
                    return null;
            }
        }
    }
}
