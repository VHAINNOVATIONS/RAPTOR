using System;
using System.Collections.Generic;
using System.Collections;
using System.Linq;
using System.Web;
using System.Configuration;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.api;

namespace gov.va.medora.mdws
{
    public class ClaimsLib
    {
        MySession mySession;

        public ClaimsLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        // This is for iterating thru a list of patients at one site, getting the prosthetics from only that site.
        public TaggedProstheticClaimArray getProstheticClaimsForPatient(string dfn)
        {
            TaggedProstheticClaimArray result = new TaggedProstheticClaimArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                ProstheticClaim[] claims = ProstheticClaim.getProstheticClaimsForPatient(mySession.ConnectionSet.BaseConnection, dfn);
                result = new TaggedProstheticClaimArray(mySession.ConnectionSet.BaseConnection.DataSource.SiteId.Id, claims);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedProstheticClaimArray getProstheticClaims(string dfn, string episodeDates)
        {
            TaggedProstheticClaimArray result = new TaggedProstheticClaimArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            string[] dates = episodeDates.Split(new char[] { ',' });
            List<string> dateList = new List<string>(dates.Length);
            for (int i = 0; i < dates.Length; i++)
            {
                if (!dateList.Contains(dates[i]))
                {
                    dateList.Add(dates[i]);
                }
            }

            try
            {
                List<ProstheticClaim> claims = ProstheticClaim.getProstheticClaims(mySession.ConnectionSet.BaseConnection, dfn, dateList);
                result = new TaggedProstheticClaimArray(mySession.ConnectionSet.BaseConnection.DataSource.SiteId.Id, claims);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        // This is for iterating thru a list of patients at one site, getting the disabilities from only that site.
        public TaggedRatedDisabilityArray getRatedDisabilitiesForPatient(string dfn)
        {
            TaggedRatedDisabilityArray result = new TaggedRatedDisabilityArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                PatientApi patApi = new PatientApi();
                RatedDisability[] disabilities = patApi.getRatedDisabiliities(mySession.ConnectionSet.BaseConnection, dfn);
                result = new TaggedRatedDisabilityArray(mySession.ConnectionSet.BaseConnection.DataSource.SiteId.Id, disabilities);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedDemographicsRecordArrays getClaimants(
            string lastName, string firstName, string middleName, string dob,
            string zipcode, string state, string city, int maxrex)
        {
            TaggedDemographicsRecordArrays result = new TaggedDemographicsRecordArrays();

            try
            {
                buildConnectionSetForGetClaimants();
                Address addr = new Address();
                if (!String.IsNullOrEmpty(zipcode))
                {
                    addr.Zipcode = zipcode;
                }
                if (!String.IsNullOrEmpty(state))
                {
                    addr.State = state;
                }
                if (!String.IsNullOrEmpty(city))
                {
                    addr.City = city;
                }
                IndexedHashtable t = Claim.getClaimants(mySession.ConnectionSet, lastName, firstName, middleName, dob, addr, maxrex);
                t = removeDups(t);
                result = new TaggedDemographicsRecordArrays(t);
            }
            catch (Exception ex)
            {
                result.fault = new FaultTO(ex.Message);
            }
            finally
            {
                mySession.ConnectionSet.disconnectAll();
            }
            return result;
        }

        internal void buildConnectionSetForGetClaimants()
        {
            Dictionary<string, AbstractConnection> cxns = new Dictionary<string, AbstractConnection>(3);

            //DataSource nptSrc = new DataSource();
            //nptSrc.Protocol = "NPT";
            //nptSrc.SiteId = new SiteId("NPT", "National Patient Table");
            //AbstractConnection nptCxn = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.NPT).getConnection(nptSrc);
            //try
            //{
            //    nptCxn.connect();
            //    cxns.Add("NPT", nptCxn);
            //}
            //catch (Exception ex)
            //{
            //}

            DataSource adrSrc = new DataSource();
            adrSrc.Protocol = "ADR";
            adrSrc.SiteId = new SiteId("ADR", "Administrative Data Repository");
            adrSrc.ConnectionString = gov.va.medora.mdo.dao.oracle.adr.AdrConstants.DEFAULT_CXN_STRING;
            AbstractConnection adrCxn = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.ADR).getConnection(adrSrc);
            try
            {
                adrCxn.connect();
                cxns.Add("ADR", adrCxn);
            }
            catch (Exception ex)
            {
            }

            DataSource vadirSrc = new DataSource();
            vadirSrc.Protocol = "VADIR";
            vadirSrc.SiteId = new SiteId("VADIR", "VA-DoD Information Repository");
            vadirSrc.ConnectionString = gov.va.medora.mdo.dao.oracle.vadir.VadirConstants.DEFAULT_CXN_STRING;
            AbstractConnection vadirCxn = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.VADIR).getConnection(vadirSrc);
            try
            {
                vadirCxn.connect();
                cxns.Add("VADIR", vadirCxn);
            }
            catch (Exception ex)
            {
            }

            DataSource vbacorpSrc = new DataSource();
            vbacorpSrc.Protocol = "VBACORP";
            vbacorpSrc.SiteId = new SiteId("VBACORP", "VBA Corp");
            vbacorpSrc.ConnectionString = gov.va.medora.mdo.dao.oracle.vbacorp.VbacorpConstants.DEFAULT_CXN_STRING;
            AbstractConnection vbacorpCxn = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.VBACORP).getConnection(vbacorpSrc);
            try
            {
                vbacorpCxn.connect();
                cxns.Add("VBACORP", vbacorpCxn);
            }
            catch (Exception ex)
            {
            }

            mySession.ConnectionSet = new ConnectionSet(cxns);
        }

        internal IndexedHashtable removeDups(IndexedHashtable t)
        {
            IndexedHashtable result = new IndexedHashtable();
            for (int i = 0; i < t.Count; i++)
            {
                string source = (string)t.GetKey(i);
                if (t.GetValue(i) == null)
                {
                    result.Add(source, null);
                    continue;
                }
                if (t.GetValue(i).GetType().Name.EndsWith("Exception"))
                {
                    gov.va.medora.mdo.exceptions.MdoException e = new gov.va.medora.mdo.exceptions.MdoException(
                        gov.va.medora.mdo.exceptions.MdoExceptionCode.DATA_INVALID, (Exception)t.GetValue(i));
                    result.Add(source,e);
                    continue;
                }
                List<Person> persons = (List<Person>)t.GetValue(i);
                List<DemographicsRecord> rex = new List<DemographicsRecord>(persons.Count);
                Hashtable ht = new Hashtable(persons.Count);
                foreach (Person p in persons)
                {
                    PersonTO pto = new PersonTO(p);
                    DemographicsRecord rec = new DemographicsRecord(pto, source);
                    long hashcode = new TOReflection.TOEqualizer(rec).HashCode;
                    if (!ht.ContainsKey(hashcode))
                    {
                        ht.Add(hashcode, rec);
                        rex.Add(rec);
                    }
                }
                result.Add(source, rex);
            }
            return result;
        }
    }
}