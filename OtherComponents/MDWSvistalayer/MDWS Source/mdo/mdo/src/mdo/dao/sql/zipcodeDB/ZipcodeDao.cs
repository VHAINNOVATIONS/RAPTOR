using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using System.Data;
using System.Data.SqlClient;
using System.Configuration;

namespace gov.va.medora.mdo.dao.sql.zipcodeDB
{
    public class ZipcodeDao
    {
        string _connectionString;
        SqlConnection _connection;

        public ZipcodeDao(string connectionString) 
        {
            if (String.IsNullOrEmpty(connectionString))
            {
                throw new ArgumentNullException("Must supply connection string");
            }
            _connectionString = connectionString;
        }

        internal void connect()
        {
            _connection = new SqlConnection(_connectionString);
            _connection.Open();
        }

        internal DataTable query(SqlCommand command)
        {
            connect();
            SqlDataAdapter adapter = new SqlDataAdapter(command);
            adapter.SelectCommand.Connection = _connection;
            DataTable results = new DataTable();
            int i = adapter.Fill(results);
            _connection.Close();
            return results;
        }

        public State[] getStates()
        {
            string statement = "SELECT DISTINCT StateName,StateAbbr,StateFIPS " +
                "FROM ZIPCodes WHERE StateFIPS<>'00';";
            SqlCommand command = new SqlCommand(statement);

            DataTable results = query(command);
            if (results == null || results.Rows == null || results.Rows.Count == 0)
            {
                return null;
            }

            State[] states = new State[results.Rows.Count];
            for (int i = 0; i < results.Rows.Count; i++)
            {
                states[i] = new State(
                    results.Rows[i]["StateName"] as string,
                    results.Rows[i]["StateAbbr"] as string,
                    results.Rows[i]["StateFIPS"] as string);
            }

            return states;
        }

        public Zipcode[] getCitiesInState(string stateAbbr)
        {
            if (string.IsNullOrEmpty(stateAbbr))
            {
                throw new ArgumentNullException("Must supply a state abbreviation");
            }
            string statement = "SELECT ZIPCode,CityName,StateName,StateAbbr FROM ZIPCodes " + 
                "WHERE StateAbbr = @STATEABBR " +
                "AND CityType<>'N' AND ZipType<>'U' AND ZipType<>'M' ORDER BY CityName,ZIPCode;";
            SqlCommand command = new SqlCommand(statement);
            SqlParameter stateAbbrParam = new SqlParameter("@STATEABBR", SqlDbType.NVarChar, 2);
            stateAbbrParam.Value = stateAbbr.ToUpper();
            command.Parameters.Add(stateAbbrParam);

            DataTable results = query(command);
            if (results == null || results.Rows == null || results.Rows.Count == 0)
            {
                return null;
            }

            Zipcode[] zips = new Zipcode[results.Rows.Count];
            for (int i = 0; i < results.Rows.Count; i++)
            {
                zips[i] = new Zipcode(
                    results.Rows[i]["ZIPCode"] as string,
                    results.Rows[i]["CityName"] as string,
                    results.Rows[i]["StateName"] as string,
                    results.Rows[i]["StateAbbr"] as string);
            }

            return zips;
        }

        public string[] matchCityAndState(string city, string stateAbbr)
        {
            if (String.IsNullOrEmpty(city) || String.IsNullOrEmpty(stateAbbr))
            {
                throw new ArgumentNullException("Must provide a city and state");
            }
            string statement = "SELECT DISTINCT CityName, CountyFIPS FROM ZipCodes " +
                "WHERE [StateAbbr] = @STATEABBR AND " +
                "CityName LIKE '%' + @CITY + '%';";  // goofy syntax - LIKE statement needs to be this format
            SqlCommand command = new SqlCommand(statement);
            SqlParameter stateParam = new SqlParameter("@STATEABBR", SqlDbType.NVarChar, 2);
            stateParam.Value = stateAbbr.ToUpper();
            SqlParameter cityParam = new SqlParameter("@CITY", SqlDbType.NVarChar, 64);
            cityParam.Value = city;
            command.Parameters.Add(stateParam);
            command.Parameters.Add(cityParam);

            DataTable results = query(command);
            if (results == null || results.Rows == null || results.Rows.Count == 0)
            {
                return null;
            }

            string[] s = new string[results.Rows.Count];
            for (int i = 0; i < results.Rows.Count; i++)
            {
                s[i] = (results.Rows[i][0] as string) + "^" + (results.Rows[i][1] as string);
            }

            return s;
        }

        public string getZipcode(string city, string stateAbbr)
        {
            if (String.IsNullOrEmpty(city) || String.IsNullOrEmpty(stateAbbr))
            {
                throw new ArgumentNullException("Must provide a city and state");
            }
            string statement = "SELECT ZIPCode FROM ZIPCodes " +
                "WHERE StateAbbr = @STATEABBR " +
                "AND CityName = @CITY;";
            SqlCommand command = new SqlCommand(statement);
            SqlParameter stateParam = new SqlParameter("@STATEABBR", SqlDbType.NVarChar, 2);
            stateParam.Value = stateAbbr.ToUpper();
            SqlParameter cityParam = new SqlParameter("@CITY", SqlDbType.NVarChar, 64);
            cityParam.Value = city.ToUpper();
            command.Parameters.Add(stateParam);
            command.Parameters.Add(cityParam);

            DataTable results = query(command);
            if (results == null || results.Rows == null || results.Rows.Count == 0)
            {
                return null;
            }
            return results.Rows[0][0] as string;
        }

        public GeographicLocation[] getGeographicLocations(string zipcode)
        {
            if (String.IsNullOrEmpty(zipcode))
            {
                throw new ArgumentNullException("Must supply zipcode");
            }
            string statement = "SELECT * FROM ZIPCodes WHERE ZIPCode = @ZIPCODE;";
            SqlCommand command = new SqlCommand(statement);
            SqlParameter zipcodeParam = new SqlParameter("@ZIPCODE", SqlDbType.NVarChar, 5);
            zipcodeParam.Value = zipcode;
            command.Parameters.Add(zipcodeParam);

            DataTable results = query(command);
            if (results == null || results.Rows == null || results.Rows.Count == 0)
            {
                return null;
            }

            GeographicLocation[] locations = new GeographicLocation[results.Rows.Count];
            for (int i = 0; i < results.Rows.Count; i++)
            {
                GeographicLocation loc = new GeographicLocation();
                loc.Zipcode = zipcode;
                loc.ZipcodeType = results.Rows[i][1] as string;
                loc.CityName = results.Rows[i][2] as string;
                loc.CityType = results.Rows[i][3] as string;
                loc.CountyName = results.Rows[i][4] as string;
                loc.CountyFips = results.Rows[i][5] as string;
                loc.StateName = results.Rows[i][6] as string;
                loc.StateAbbreviation = results.Rows[i][7] as string;
                loc.StateFips = results.Rows[i][8] as string;
                loc.MsaCode = results.Rows[i][9] as string;
                loc.AreaCode = results.Rows[i][10] as string;
                loc.TimeZone = results.Rows[i][11] as string;
                loc.Utc = getInt(results.Rows[i][12]);
                loc.DaylightSavings = ((results.Rows[i][13] as string) == "Y");
                loc.Latitude = getDouble(results.Rows[i][14]);
                loc.Longitude = getDouble(results.Rows[i][15]);
                locations[i] = loc;
            }

            return locations;
        }

        internal int getInt(object arg)
        {
            try
            {
                return Convert.ToInt32(arg);
            }
            catch (Exception)
            {
                return 0;
            }
        }

        internal double getDouble(object arg)
        {
            try
            {
                return Convert.ToDouble(arg);
            }
            catch (Exception)
            {
                return 0;
            }
        }
    }
}
