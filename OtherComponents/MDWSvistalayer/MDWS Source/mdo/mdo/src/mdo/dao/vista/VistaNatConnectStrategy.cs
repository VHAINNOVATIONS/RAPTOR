using System;
using System.Collections.Generic;
using System.Text;
using System.Net;
using System.Net.Sockets;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaNatConnectStrategy : IConnectStrategy
    {
        // Needs to be VistaConnection to share the connection's socket and port
        VistaConnection cxn;

        DataSource dataSource;

        public VistaNatConnectStrategy(AbstractConnection cxn)
        {
            this.cxn = (VistaConnection)cxn;
            this.dataSource = cxn.DataSource;
        }

        public void connect()
        {
            cxn.IsConnected = false;

            if (dataSource == null)
            {
                throw new ArgumentNullException("No data source");
            }
            string hostname = dataSource.Provider;
            if (String.IsNullOrEmpty(hostname))
            {
                throw new ArgumentNullException("No provider (hostname)");
            }

            //Who am I?
            IPHostEntry hostEntry = Dns.GetHostEntry("localhost");
            IPAddress myIP = (IPAddress)Dns.GetHostEntry(hostEntry.HostName).AddressList[0];

            //Config my client socket and connnect to VistA
            IPAddress vistaIP = null;
            if (!IPAddress.TryParse(hostname, out vistaIP)) // see if hostname is actually IP address (will get stuck in vistaIP, if so) - if not, get IP address from hostname
            {
                try
                {
                    vistaIP = (IPAddress)Dns.GetHostEntry(hostname).AddressList[0];
                }
                catch (SocketException se)
                {
                    throw new ConnectionException("No route to host " + hostname, se);
                }
            }
            IPEndPoint vistaEndPoint = new IPEndPoint(vistaIP, cxn.port);
            cxn.socket = new Socket(AddressFamily.InterNetwork, SocketType.Stream, ProtocolType.Tcp);
            cxn.socket.SetSocketOption(SocketOptionLevel.Socket, SocketOptionName.ReceiveTimeout, cxn.ConnectTimeout);
            try
            {
                cxn.socket.Connect(vistaEndPoint);
            }
            catch (SocketException se)
            {
                throw new ConnectionException("No VistA listener at " + hostname + ", port " + cxn.port, se);
            }
            if (!cxn.socket.Connected)
            {
                throw new ConnectionException("Unable to connect to " + hostname + ", port " + cxn.port);
            }

            //Build the request message
            int COUNT_WIDTH = 3;
            string request = "[XWB]10" + COUNT_WIDTH.ToString() + "04\nTCPConnect50" +
                StringUtils.strPack(myIP.ToString(), COUNT_WIDTH) +
                "f0" + StringUtils.strPack(Convert.ToString(0), COUNT_WIDTH) + "f0" +
                StringUtils.strPack(hostEntry.HostName, COUNT_WIDTH) + "f\x0004";

            string reply = "";
            try
            {
                reply = (string)cxn.query(request);
            }
            catch (SocketException se)
            {
                throw new ConnectionException("No VistA listener at " + hostname + ", port " + cxn.port, se);
            }
            if (reply != "accept")
            {
                cxn.socket.Close();
                throw new Exception("Unaccepted by " + hostname);
            }

            cxn.socket.SetSocketOption(SocketOptionLevel.Socket, SocketOptionName.ReceiveTimeout, cxn.ReadTimeout);

            request = "[XWB]11302\x00010\rXUS INTRO MSG54f\x0004";
            reply = (string)cxn.query(request);

            cxn.IsConnected = true;
        }

    }
}
