using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using Oracle.DataAccess.Client;
using Oracle.DataAccess.Types;
using gov.va.medora.mdo.exceptions;
//using System.Data.OracleClient;

namespace gov.va.medora.mdo.dao.oracle
{
    public class OracleClaimsDao
    {
        MdoOracleConnection myCxn;

        public OracleClaimsDao(MdoOracleConnection cxn)
        {
            myCxn = cxn;
        }

        public List<Person> getClaimants(string sql)
        {
            OracleDataReader rdr = (OracleDataReader)myCxn.query(sql);
            List<Person> matches = toPersonList(rdr);
            if (matches == null || matches.Count == 0)
            {
                return null;
            }

            return matches;
        }

        internal List<Person> toPersonList(OracleDataReader rdr)
        {
            List<Person> lst = new List<Person>();
            while (rdr.Read())
            {
                lst.Add(toPerson(rdr));
            }
            return lst;
        }

        internal Person toPerson(OracleDataReader rdr)
        {
            Person result = new Person();
            string s = "";
            if (!rdr.IsDBNull(rdr.GetOrdinal("Id")))
            {
                s = rdr["Id"].ToString().Trim();
                if (s != "")
                {
                    result.Id = s;
                }
            }
            if (!rdr.IsDBNull(rdr.GetOrdinal("FirstName")) &&
                !rdr.IsDBNull(rdr.GetOrdinal("LastName")))
            {
                string name = rdr["LastName"].ToString().Trim() + ',' +
                              rdr["FirstName"].ToString().Trim();
                if (name != ",")
                {
                    if (!rdr.IsDBNull(rdr.GetOrdinal("MiddleName")))
                    {
                        name += ' ' + rdr["MiddleName"].ToString().Trim();
                    }
                    result.Name = new PersonName(name.Trim());
                }
            }
            //if (!rdr.IsDBNull(rdr.GetOrdinal("SSN")))
            //{
            //    result.SSN = new SocSecNum(rdr["SSN"].ToString());
            //}
            if (!rdr.IsDBNull(rdr.GetOrdinal("DOB")))
            {
                s = rdr["DOB"].ToString().Trim();
                if (s != "")
                {
                    result.DOB = s;
                }
            }
            if (!rdr.IsDBNull(rdr.GetOrdinal("Gender")))
            {
                s = rdr["Gender"].ToString().Trim();
                if (s != "")
                {
                    result.Gender = s;
                }
            }
            DemographicSet demoSet = new DemographicSet();
            demoSet.StreetAddresses = addAddresses(rdr);
            demoSet.PhoneNumbers = addPhones(rdr);
            demoSet.EmailAddresses = addEmails(rdr);
            result.Demographics = new Dictionary<string, DemographicSet>();
            result.Demographics.Add(myCxn.DataSource.Protocol, demoSet);
            return result;
        }

        internal List<Address> addAddresses(OracleDataReader rdr)
        {
            Address addr = new Address();
            bool fHasData = false;
            string s = "";
            if (!rdr.IsDBNull(rdr.GetOrdinal("Street1")))
            {
                s = rdr["Street1"].ToString().Trim();
                if (s != "")
                {
                    addr.Street1 = s;
                    fHasData = true;
                }
            }
            if (!rdr.IsDBNull(rdr.GetOrdinal("Street2")))
            {
                s = rdr["Street2"].ToString().Trim();
                if (s != "")
                {
                    addr.Street2 = s;
                    fHasData = true;
                }
            }
            if (rdr.GetSchemaTable().Columns.Contains("Street3") && !rdr.IsDBNull(rdr.GetOrdinal("Street3")))
            {
                s = rdr["Street3"].ToString().Trim();
                if (s != "")
                {
                    addr.Street3 = s;
                    fHasData = true;
                }
            }
            if (!rdr.IsDBNull(rdr.GetOrdinal("City")))
            {
                s = rdr["City"].ToString().Trim();
                if (s != "")
                {
                    addr.City = s;
                    fHasData = true;
                }
            }
            if (!rdr.IsDBNull(rdr.GetOrdinal("State")))
            {
                s = rdr["State"].ToString().Trim();
                if (s != "")
                {
                    addr.State = s;
                    fHasData = true;
                }
            }
            if (!rdr.IsDBNull(rdr.GetOrdinal("Zipcode")))
            {
                s = rdr["Zipcode"].ToString().Trim();
                if (s != "")
                {
                    addr.Zipcode = s;
                    fHasData = true;
                }
            }
            if (!fHasData)
            {
                return null;
            }
            List<Address> result = new List<Address>(1);
            result.Add(addr);
            return result;
        }

        internal List<PhoneNum> addPhones(OracleDataReader rdr)
        {
            List<PhoneNum> result = null;
            if (rdr.IsDBNull(rdr.GetOrdinal("PhoneNumber")))
            {
                return result;
            }

            string s = rdr["PhoneNumber"].ToString().Trim();
            if (s == "")
            {
                return result;
            }

            result = new List<PhoneNum>(2);

            PhoneNum phoneNum = new PhoneNum();
            phoneNum.Number = s;
            if (!rdr.IsDBNull(rdr.GetOrdinal("PhoneType")))
            {
                s = rdr["PhoneType"].ToString().Trim();
                if (s != "")
                {
                    phoneNum.Description = s;
                }
            }
            result.Add(phoneNum);

            if (rdr.GetSchemaTable().Columns.Contains("PhoneNumber2") && !rdr.IsDBNull(rdr.GetOrdinal("PhoneNumber2")))
            {
                s = rdr["PhoneNumber2"].ToString().Trim();
                if (s == "")
                {
                    return result;
                }
                phoneNum = new PhoneNum();
                phoneNum.Number = s;
                if (!rdr.IsDBNull(rdr.GetOrdinal("PhoneType2")))
                {
                    s = rdr["PhoneType2"].ToString().Trim();
                    if (s != "")
                    {
                        phoneNum.Description = s;
                    }
                }
                result.Add(phoneNum);
            }
            return result;
        }

        internal List<EmailAddress> addEmails(OracleDataReader rdr)
        {
            if (rdr.IsDBNull(rdr.GetOrdinal("Email")))
            {
                return null;
            }
            else
            {
                string s = rdr["Email"].ToString().Trim();
                if (s == "")
                {
                    return null;
                }
                List<EmailAddress> result = new List<EmailAddress>(1);
                result.Add(new EmailAddress(s));
                return result;
            }
        }

    }

    public abstract class BuildGetClaimantsRequestTemplate
    {
        public BuildGetClaimantsRequestTemplate() { }

        public abstract string Tables { get; }
        public abstract string Fields { get; }
        public abstract string Where { get; }
        public abstract StringDictionary FieldNames { get; }

        public string buildGetClaimantsRequest(string lastName, string firstName, string middleName, string dob, Address addr, int maxrex)
        {
            bool fHasLastName = false;
            if (!String.IsNullOrEmpty(lastName))
            {
                if (!PersonName.isValid(lastName))
                {
                    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Missing or invalid last Name");
                }
                lastName = lastName.ToUpper();
                fHasLastName = true;
            }
            bool fHasFirstName = false;
            if (!String.IsNullOrEmpty(firstName))
            {
                if (!fHasLastName)
                {
                    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "No firstName searches without lastName");
                }
                if (!PersonName.isValid(firstName))
                {
                    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid first name");
                }
                firstName = firstName.ToUpper();
                fHasFirstName = true;
            }
            if (!String.IsNullOrEmpty(middleName))
            {
                if (!fHasLastName)
                {
                    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "No middleName searches without lastName");
                }
                if (!fHasFirstName)
                {
                    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "No middleName searches without firstName");
                }
                if (!PersonName.isValid(middleName))
                {
                    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid middle name/initial");
                }
                middleName = middleName.ToUpper();
            }
            if (!String.IsNullOrEmpty(dob))
            {
                if (!fHasLastName)
                {
                    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "No DOB searches without lastName");
                }
                if (!utils.DateUtils.isWellFormedDatePart(dob))
                {
                    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid DOB");
                }
            }

            string myWhere = Where;
            if (!String.IsNullOrEmpty(lastName))
            {
                myWhere += " and " + buildGetClaimantsWhereNamePiece(lastName, firstName, middleName, dob);
            }
            if (hasGeographicParams(addr))
            {
                string wherePiece = buildGetClaimantsWhereAddressPiece(addr);
                if (wherePiece != "")
                {
                    myWhere += " and " + wherePiece;
                }
            }
            if (maxrex > 0)
            {
                myWhere += " and rownum<=" + maxrex;
            }
            return "select " + Fields + " from " + Tables + " where " + myWhere;
        }

        internal bool hasGeographicParams(Address addr)
        {
            if (addr == null)
            {
                return false;
            }
            if (!String.IsNullOrEmpty(addr.Zipcode) ||
                !String.IsNullOrEmpty(addr.State) ||
                !String.IsNullOrEmpty(addr.City))
            {
                return true;
            }
            return false;
        }

        internal string buildGetClaimantsWhereNamePiece(string lastName, string firstName, string middleName, string dob)
        {
            string whereClause = FieldNames["LastName"] + "='" + lastName + "'";
            if (!String.IsNullOrEmpty(firstName))
            {
                whereClause += " and " + FieldNames["FirstName"] + " like '" + firstName + "%'";
            }
            if (!String.IsNullOrEmpty(middleName))
            {
                whereClause += " and " + FieldNames["MiddleName"] + " like '" + middleName + "%'";
            }
            if (!String.IsNullOrEmpty(dob))
            {
                string fldName = FieldNames["DOB"];
                if (fldName[0] == '@')
                {
                    whereClause += " and " + fldName.Substring(1) + "=to_date('" + dob + "','YYYYMMDD')";
                }
                else
                {
                    whereClause += " and " + fldName + "='" + dob + "'";
                }
            }
            return whereClause;
        }

        internal string buildGetClaimantsWhereAddressPiece(Address addr)
        {
            string whereClause = "";
            if (!String.IsNullOrEmpty(addr.Zipcode))
            {
                whereClause += FieldNames["Zipcode"] + "='" + addr.Zipcode + "'";
            }
            else
            {
                if (!String.IsNullOrEmpty(addr.State))
                {
                    whereClause = FieldNames["State"] + "='" + addr.State + "'";
                }
                if (!String.IsNullOrEmpty(addr.City))
                {
                    whereClause += (whereClause == "" ? "" : " and ") +
                        FieldNames["City"] + "='" + addr.City + "'";
                }
            }
            return whereClause;
        }
    }
}
